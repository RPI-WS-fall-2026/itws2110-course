// web/src/session.jsx -- who is logged in, for every page.
//
// Log in is not part of the midterm. The fake API acts as one person (Alex, unless the
// address says ?as=priya, ?as=ra or ?as=guest), and this file asks it who that is. In week
// 12 log in arrives, and the pages that read useSession() do not change.
//
// Why a context: the header, the shelf and the item page all need the user. A context
// hands it to the components that ask, without passing it through every component.
// https://react.dev/learn/passing-data-deeply-with-context
import { createContext, useContext, useEffect, useState } from 'react';
import * as api from './api.js';

const SessionContext = createContext(null);

export function SessionProvider({ children }) {
  const [user, setUser] = useState(null);
  // 'checking' until GET /api/user answers, so a page does not flash "not allowed" first.
  const [status, setStatus] = useState('checking');

  useEffect(() => {
    let ignore = false;
    api.currentUser()
      .then((u) => { if (!ignore) setUser(u); })
      .catch(() => { if (!ignore) setUser(null); })   // 401: nobody is logged in
      .finally(() => { if (!ignore) setStatus('ready'); });
    return () => { ignore = true; };
  }, []);

  return <SessionContext.Provider value={{ user, status }}>{children}</SessionContext.Provider>;
}

export function useSession() {
  const session = useContext(SessionContext);
  if (!session) throw new Error('useSession() must be inside <SessionProvider>');
  return session;
}
