// web/src/session.jsx -- who is logged in, for every page.
//
// The session itself lives in Laravel (a cookie the browser sends with every request).
// This file asks the API who that is, and gives the answer to any component that calls
// useSession(). Why a context: the header, the shelf, the item page and the admin page all
// need it, and a context hands it to them without passing it through every component.
// https://react.dev/learn/passing-data-deeply-with-context
import { createContext, useContext, useEffect, useState } from 'react';
import * as api from './api.js';

const SessionContext = createContext(null);

export function SessionProvider({ children }) {
  const [user, setUser] = useState(null);
  // 'checking' until GET /api/user answers, so a page that needs a login does not flash
  // "please log in" at someone who is logged in.
  const [status, setStatus] = useState('checking');

  useEffect(() => {
    let ignore = false;
    api.currentUser()
      .then((u) => { if (!ignore) setUser(u); })
      .catch(() => { if (!ignore) setUser(null); })   // 401: nobody is logged in
      .finally(() => { if (!ignore) setStatus('ready'); });
    return () => { ignore = true; };
  }, []);

  const value = {
    user,
    status,
    async login(credentials) { const u = await api.login(credentials); setUser(u); return u; },
    async register(fields) { const u = await api.register(fields); setUser(u); return u; },
    async logout() { await api.logout(); setUser(null); },
  };
  return <SessionContext.Provider value={value}>{children}</SessionContext.Provider>;
}

export function useSession() {
  const session = useContext(SessionContext);
  if (!session) throw new Error('useSession() must be inside <SessionProvider>');
  return session;
}
