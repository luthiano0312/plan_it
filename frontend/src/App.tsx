import { Route, Routes } from 'react-router'
import Layout from './components/Layout'
import ItemPage from './pages/ItemPage'
import NewItemPage from './pages/NewItemPage'
import NowPage from './pages/NowPage'

function EmConstrucao() {
  return <p>Página em construção.</p>
}

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<NowPage />} />
        <Route path="novo" element={<NewItemPage />} />
        <Route path="items" element={<EmConstrucao />} />
        <Route path="items/:id" element={<ItemPage />} />
      </Route>
    </Routes>
  )
}
