import { Route, Routes } from 'react-router'
import Layout from './components/Layout'
import AllItemsPage from './pages/AllItemsPage'
import ItemPage from './pages/ItemPage'
import NewItemPage from './pages/NewItemPage'
import NowPage from './pages/NowPage'

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<NowPage />} />
        <Route path="novo" element={<NewItemPage />} />
        <Route path="items" element={<AllItemsPage />} />
        <Route path="items/:id" element={<ItemPage />} />
      </Route>
    </Routes>
  )
}
