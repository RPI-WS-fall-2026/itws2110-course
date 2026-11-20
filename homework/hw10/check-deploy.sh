#!/usr/bin/env bash
# check-deploy.sh -- Homework 10's check. PASS or FAIL for each thing a
# deployed copy of your project must do. The same script checks the production image on
# your laptop and the copy running on Azure:
#
#   bash homework/hw10/check-deploy.sh http://localhost:8090 [resource]
#   bash homework/hw10/check-deploy.sh https://<your app>.azurecontainerapps.io [resource]
#
# RESOURCE is the path of your core resource (default: items). Needs curl; nothing else.
#
# Checks 7, 8 and 10 log in the way your pages do (Sanctum SPA authentication: the CSRF cookie,
# sent back in the X-XSRF-TOKEN header, with a Referer), as check-login.sh did last week.
set -u
BASE=${1:-http://localhost:8090}
RESOURCE=${2:-items}
BASE=${BASE%/}
JAR=$(mktemp) && OLDJAR=$(mktemp) && BODY=$(mktemp) && HEAD=$(mktemp)
trap 'rm -f "$JAR" "$OLDJAR" "$BODY" "$HEAD"' EXIT
STAMP=$(date +%s)
EMAIL="deploy-check-$STAMP@example.edu"
PASSWORD="correct horse battery"
# The account this script made the last time it checked this address: check 10 logs in
# with it again, to prove a new deploy kept the data.
MEMO="${TMPDIR:-/tmp}/check-deploy-$(printf '%s' "$BASE" | tr -c 'A-Za-z0-9' '_').txt"
pass=0; fail=0; skip=0

ok()   { printf 'PASS  %s\n' "$1"; pass=$((pass + 1)); }
no()   { printf 'FAIL  %s\n      got %s\n' "$1" "$2"; fail=$((fail + 1)); }
skp()  { printf 'SKIP  %s\n' "$1"; skip=$((skip + 1)); }
first_line() {
  msg=$(tr -d '\r' < "$BODY" | tr '\n' ' ' | sed -n 's/.*"message" *: *"\([^"]*\)".*/\1/p')
  if [ -n "$msg" ]; then printf '"%s"' "$msg" | head -c 160; echo
  else tr -d '\r' < "$BODY" | tr '\n' ' ' | tr -s ' ' | head -c 160; echo; fi
}
# get PATH -> prints the status code; body in $BODY, response headers in $HEAD.
get() { curl -s -o "$BODY" -D "$HEAD" -w '%{http_code}' --max-time 60 -H "Accept: ${2:-*/*}" "$BASE$1"; }
ctype() { tr -d '\r' < "$HEAD" | awk 'tolower($1) == "content-type:" { print tolower($2) }' | tail -n 1; }
csrf() {
  curl -s -b "$1" -c "$1" -H "Referer: $BASE/" -o /dev/null "$BASE/sanctum/csrf-cookie"
  local raw
  raw=$(awk '$6 == "XSRF-TOKEN" { print $7 }' "$1" | tail -n 1)
  printf '%b' "${raw//%/\\x}"
}
# req JAR METHOD PATH [JSON] -> prints the status code; the body lands in $BODY.
req() {
  local jar=$1 method=$2 path=$3 data=${4:-} token=""
  if [ "$method" != "GET" ]; then token=$(csrf "$jar"); fi
  curl -s -o "$BODY" -D "$HEAD" -w '%{http_code}' --max-time 60 -X "$method" "$BASE$path" \
    -b "$jar" -c "$jar" \
    -H 'Accept: application/json' -H 'Content-Type: application/json' \
    -H "Referer: $BASE/" -H "X-XSRF-TOKEN: $token" \
    ${data:+--data "$data"}
}

echo "Checking $BASE (resource: $RESOURCE)"

# 1. the container is up. A copy that scaled to zero takes about ten seconds to wake,
#    so this one check waits for it.
code=000
for _ in 1 2 3 4 5 6; do
  code=$(get /up)
  [ "$code" = "200" ] && break
  sleep 5
done
if [ "$code" = "200" ]; then ok '1. GET /up -> 200: the container started and its migrations ran'
else no '1. GET /up -> 200' "$code (000 means nothing answered at that address)"; fi

# 2. the React build is served by the same container
code=$(get /)
if [ "$code" = "200" ] && grep -qi '<div id="root"' "$BODY" && grep -q '/assets/' "$BODY"; then
  ok '2. GET / -> the React app (index.html from web/dist, not a Vite dev server)'
elif [ "$code" = "200" ] && grep -q '/src/main' "$BODY"; then
  no '2. GET / -> the React BUILD' "200, but this page loads /src/main.jsx: it is the Vite dev server, not the production image"
else
  no '2. GET / -> the React app' "$code $(ctype): $(first_line)"
fi
ASSET=$(grep -o '/assets/[A-Za-z0-9._-]*\.js' "$BODY" | head -n 1)

# 3. a built file is sent as a file
if [ -n "$ASSET" ]; then
  code=$(get "$ASSET")
  case "$(ctype)" in
    *javascript*) [ "$code" = "200" ] && ok "3. GET $ASSET -> 200, JavaScript: the server sends the built files itself" || no "3. GET $ASSET -> 200" "$code" ;;
    *) no "3. GET $ASSET -> JavaScript" "$code $(ctype)" ;;
  esac
else
  no '3. a built /assets/*.js file is served' 'no /assets/ script in the page from check 2'
fi

# 4. a deep link loads the app (the fallback route)
code=$(get "/a/page/that/only/react/knows" text/html)
if [ "$code" = "200" ] && grep -qi '<div id="root"' "$BODY"; then
  ok '4. GET /a/page/that/only/react/knows -> the React app (the fallback route in routes/web.php)'
else
  no '4. a deep link -> the React app' "$code: $(first_line)"
fi

# 5. the API answers, from the database
code=$(get "/api/$RESOURCE" application/json)
case "$(ctype)" in
  *json*) if [ "$code" = "200" ]; then ok "5. GET /api/$RESOURCE -> 200 JSON: Laravel reached its database"
          elif [ "$code" = "404" ]; then no "5. GET /api/$RESOURCE -> 200 JSON" "404: $(first_line)  (is $RESOURCE your resource? The second argument is the path under /api/; it defaults to items)"
          else no "5. GET /api/$RESOURCE -> 200 JSON" "$code: $(first_line)"; fi ;;
  *) no "5. GET /api/$RESOURCE -> 200 JSON" "$code $(ctype): $(first_line)" ;;
esac

# 6. errors say nothing about the code (APP_DEBUG=false)
code=$(get "/api/no-such-route-$STAMP" application/json)
if [ "$code" = "404" ] && ! grep -Eq '"(trace|exception|file)" *:' "$BODY"; then
  ok '6. GET /api/no-such-route -> 404 with no stack trace (APP_DEBUG is false)'
elif [ "$code" = "404" ]; then
  no '6. a 404 with no stack trace' '404, but the body has "exception" / "trace": APP_DEBUG is true'
else
  no '6. GET /api/no-such-route -> 404 JSON' "$code: $(first_line)"
fi

# 10 (first half, done before a new account is made): last run's account still logs in.
OLD=""
[ -f "$MEMO" ] && OLD=$(cat "$MEMO")
old_code=""
if [ -n "$OLD" ]; then old_code=$(req "$OLDJAR" POST /api/login "{\"email\":\"$OLD\",\"password\":\"$PASSWORD\"}"); old_msg=$(first_line); fi

# 7. register: a write, through the session and the CSRF check
code=$(req "$JAR" POST /api/register "{\"name\":\"Deploy Check\",\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\",\"password_confirmation\":\"$PASSWORD\"}")
if [ "$code" = "201" ]; then
  ok '7. POST /api/register -> 201: a row was written to the production database'
  printf '%s' "$EMAIL" > "$MEMO"
else
  no '7. POST /api/register -> 201' "$code: $(first_line)"
fi

# 8. the session works
code=$(req "$JAR" GET /api/user)
if [ "$code" = "200" ] && grep -q "$EMAIL" "$BODY"; then ok '8. GET /api/user with the cookie -> 200: the login survives between requests'
else no '8. GET /api/user with the cookie -> 200' "$code: $(first_line)"; fi

# 9. over https, the session cookie is marked Secure
case "$BASE" in
  https://*)
    # Field 4 of a cookie-jar line is TRUE for a Secure cookie. Skip XSRF-TOKEN: the session cookie is the HttpOnly one.
    insecure=$(grep '^#HttpOnly_' "$JAR" | awk '$4 != "TRUE" { print $6 }' | head -n 1)
    if grep -q '^#HttpOnly_' "$JAR" && [ -z "$insecure" ]; then ok '9. the session cookie is Secure: it is never sent over plain http'
    else no '9. the session cookie is Secure' "cookie \"$insecure\" is not: SESSION_SECURE_COOKIE=true is missing, or trustProxies is"; fi ;;
  *) skp '9. the session cookie is Secure (checked only for an https address)' ;;
esac

# 10. the account from the last run is still there
if [ -z "$OLD" ]; then
  skp '10. the data survives a new deploy (run this check again after your next deploy)'
elif [ "$old_code" = "200" ]; then
  ok "10. the account from the last run ($OLD) still logs in: the deploy kept the data"
else
  no '10. the account from the last run still logs in' "$old_code: $old_msg  (the database was emptied: migrate:fresh, a seeder, or down -v)"
fi

# 11. over https, the browser is told never to use plain http for this site again
case "$BASE" in
  https://*)
    get / > /dev/null
    hsts=$(tr -d '\r' < "$HEAD" | awk 'tolower($1) == "strict-transport-security:" { $1 = ""; print }' | tail -n 1)
    case "$hsts" in
      *max-age=0*|"") no '11. Strict-Transport-Security is sent' "${hsts:-no such header}: the SecurityHeaders middleware is not on (bootstrap/app.php)" ;;
      *max-age=*) ok "11. Strict-Transport-Security:$hsts: browsers will refuse plain http here" ;;
      *) no '11. Strict-Transport-Security has a max-age' "$hsts" ;;
    esac ;;
  *) skp '11. Strict-Transport-Security (checked only for an https address: browsers ignore it over http)' ;;
esac

echo "$pass passed, $fail failed, $skip skipped"
[ "$fail" -eq 0 ]
