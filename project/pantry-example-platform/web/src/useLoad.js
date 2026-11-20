// web/src/useLoad.js -- the loading pattern from MIDTERM_APPENDIX.md section B, once.
//
// Four pages load data, and each needs the same four outcomes: loading, ready, not
// found (a 404), error. Writing the appendix's effect four times is four chances to forget
// the `ignore` flag. A custom hook shares the logic, and each page still gets its own
// state (week 6, drill 14: "a custom hook shares logic, not state").
//
//   const { status, data, retry } = useLoad(() => getItem(id), [id]);
import { useEffect, useState } from 'react';

export function useLoad(load, deps) {
  // One status value, not isLoading + isError booleans that can disagree (week 6, drill 9).
  const [state, setState] = useState({ status: 'loading', data: null, error: null });
  const [attempt, setAttempt] = useState(0);

  useEffect(() => {
    // The page may be gone, or showing another item, before the answer arrives.
    let ignore = false;
    setState((s) => (s.status === 'loading' ? s : { status: 'loading', data: null, error: null }));
    load()
      .then((data) => { if (!ignore) setState({ status: 'ready', data, error: null }); })
      .catch((error) => {
        if (ignore) return;
        const status = error?.status === 404 ? 'not-found'
          : error?.status === 401 || error?.status === 403 ? 'denied' : 'error';
        setState({ status, data: null, error });
      });
    return () => { ignore = true; };
    // `load` is a new function on every render; the caller's deps say when to reload.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [...deps, attempt]);

  return {
    ...state,
    /** Load again: the "Try again" button on an error. */
    retry: () => setAttempt((n) => n + 1),
    /** Change the loaded data in place after a save, without loading again. */
    setData: (update) => setState((s) => ({ ...s, data: typeof update === 'function' ? update(s.data) : update })),
  };
}
