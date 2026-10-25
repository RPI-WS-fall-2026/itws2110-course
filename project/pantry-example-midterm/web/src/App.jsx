// The pages and their addresses. MIDTERM_APPENDIX.md section D, the React Router way:
// each page has its own address, so the back button works and a link to an item can be
// shared. (The template's Part 3 asks for client-side routing; the appendix allows a
// piece of state instead, and says the finished app should use the router.)
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import Layout from './components/Layout.jsx';
import ShelfPage from './pages/ShelfPage.jsx';
import ItemPage from './pages/ItemPage.jsx';
import { EditItemPage, NewItemPage } from './pages/ItemFormPage.jsx';
import NotFoundPage from './pages/NotFoundPage.jsx';
import { SessionProvider } from './session.jsx';

export default function App() {
  return (
    <SessionProvider>
      <BrowserRouter>
        <Routes>
          <Route element={<Layout />}>
            <Route index element={<ShelfPage />} />
            <Route path="items/new" element={<NewItemPage />} />
            <Route path="items/:id" element={<ItemPage />} />
            <Route path="items/:id/edit" element={<EditItemPage />} />
            <Route path="*" element={<NotFoundPage />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </SessionProvider>
  );
}
