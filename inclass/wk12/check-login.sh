#!/usr/bin/env bash
# check-login.sh -- the week 12 login lab's check. Nine requests against YOUR api/, PASS or FAIL each.
#
#   bash inclass/wk12/check-login.sh http://localhost:8010 [resource]
#
# BASE is your web port (Vite forwards /api and /sanctum to Laravel). RESOURCE is the path of
# your core resource (default: items), used only for check 7. Needs curl; nothing else.
#
# Sanctum "SPA authentication": every write must carry the CSRF token Laravel hands out as the
# XSRF-TOKEN cookie, sent back URL-decoded in the X-XSRF-TOKEN header, and the request must
# look like it came from your page (Referer), or Sanctum ignores the session cookie. That is
# exactly what web/src/api.js does; this script does it with curl.
set -u
BASE=${1:-http://localhost:8010}
RESOURCE=${2:-items}
BASE=${BASE%/}
JAR=$(mktemp) && EMPTY=$(mktemp) && BODY=$(mktemp)
trap 'rm -f "$JAR" "$EMPTY" "$BODY"' EXIT
STAMP=$(date +%s)
EMAIL="check-$STAMP@example.edu"
PASSWORD="correct horse battery"
pass=0; fail=0

ok()   { printf 'PASS  %s\n' "$1"; pass=$((pass + 1)); }
no()   { printf 'FAIL  %s\n      got %s\n' "$1" "$2"; fail=$((fail + 1)); }
first_line() { tr -d '\r' < "$BODY" | head -c 160; echo; }

# Fetch the CSRF cookie into the given jar and print the token, URL-decoded.
csrf() {
  curl -s -b "$1" -c "$1" -H "Referer: $BASE/" -o /dev/null "$BASE/sanctum/csrf-cookie"
  local raw
  raw=$(awk '$6 == "XSRF-TOKEN" { print $7 }' "$1" | tail -n 1)
  printf '%b' "${raw//%/\\x}"
}

# req JAR METHOD PATH [JSON] -> prints the status code; the body lands in $BODY.
req() {
  local jar=$1 method=$2 path=$3 data=${4:-}
  local token=""
  if [ "$method" != "GET" ]; then token=$(csrf "$jar"); fi
  curl -s -o "$BODY" -w '%{http_code}' -X "$method" "$BASE$path" \
    -b "$jar" -c "$jar" \
    -H 'Accept: application/json' -H 'Content-Type: application/json' \
    -H "Referer: $BASE/" -H "X-XSRF-TOKEN: $token" \
    ${data:+--data "$data"}
}

echo "Checking $BASE (resource: $RESOURCE)"

# 1. register, with an extra field the server must ignore
code=$(req "$JAR" POST /api/register "{\"name\":\"Check Script\",\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\",\"password_confirmation\":\"$PASSWORD\",\"role\":\"admin\"}")
if [ "$code" = "201" ] && ! grep -q '"role" *: *"admin"' "$BODY"; then
  ok '1. POST /api/register -> 201, and the extra "role":"admin" in the body was ignored'
else
  no '1. POST /api/register -> 201 without role admin' "$code: $(first_line)"
fi

# 2. wrong password
code=$(req "$EMPTY" POST /api/login "{\"email\":\"$EMAIL\",\"password\":\"not it\"}")
wrong_pw=$(tr -d '\r' < "$BODY")
if [ "$code" = "422" ]; then ok '2. POST /api/login with the wrong password -> 422'; else no '2. POST /api/login with the wrong password -> 422' "$code: $(first_line)"; fi

# 3. unknown email gives the same answer
code=$(req "$EMPTY" POST /api/login "{\"email\":\"nobody-$STAMP@example.edu\",\"password\":\"not it\"}")
unknown=$(tr -d '\r' < "$BODY")
if [ "$code" = "422" ] && [ "$wrong_pw" = "$unknown" ]; then
  ok '3. the wrong-password and unknown-email messages are identical'
else
  no '3. one message for a wrong password and an unknown email' "$code: $(first_line)"
fi

# 4. a real login sets a session cookie
: > "$JAR"
code=$(req "$JAR" POST /api/login "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")
if [ "$code" = "200" ] && grep -qi 'session' "$JAR"; then ok '4. POST /api/login -> 200 and a session cookie'; else no '4. POST /api/login -> 200 and a session cookie' "$code: $(first_line)"; fi

# 5. who am I, with the cookie
code=$(req "$JAR" GET /api/user)
if [ "$code" = "200" ] && grep -q "$EMAIL" "$BODY"; then ok '5. GET /api/user with the cookie -> 200, with the email'; else no '5. GET /api/user with the cookie -> 200 with the email' "$code: $(first_line)"; fi

# 6. who am I, without
: > "$EMPTY"
code=$(req "$EMPTY" GET /api/user)
if [ "$code" = "401" ]; then ok '6. GET /api/user without a cookie -> 401'; else no '6. GET /api/user without a cookie -> 401' "$code: $(first_line)"; fi

# 7. a write route without a cookie
: > "$EMPTY"
code=$(req "$EMPTY" POST "/api/$RESOURCE" '{}')
if [ "$code" = "401" ]; then ok "7. POST /api/$RESOURCE without a cookie -> 401 (not 501)"; else no "7. POST /api/$RESOURCE without a cookie -> 401" "$code: $(first_line)"; fi

# 8. log out, then the cookie is worthless
code=$(req "$JAR" POST /api/logout)
code2=$(req "$JAR" GET /api/user)
if [ "$code" = "204" ] && [ "$code2" = "401" ]; then ok '8. POST /api/logout -> 204, and GET /api/user is 401 again'; else no '8. POST /api/logout -> 204 then GET /api/user -> 401' "$code then $code2: $(first_line)"; fi

# 9. throttle: 21 bad logins on one (unknown) email from one address
: > "$EMPTY"
throttled=no
for i in $(seq 1 21); do
  code=$(req "$EMPTY" POST /api/login "{\"email\":\"throttle-$STAMP@example.edu\",\"password\":\"not it\"}")
  if [ "$code" = "429" ]; then throttled=yes; break; fi
done
if [ "$throttled" = "yes" ]; then ok '9. twenty-one bad logins -> 429 (throttle:login)'; else no '9. twenty-one bad logins -> 429' "never saw a 429 (last: $code)"; fi

echo "$pass passed, $fail failed"
[ "$fail" -eq 0 ]
