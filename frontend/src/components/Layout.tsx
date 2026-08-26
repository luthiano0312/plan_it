import { NavLink, Outlet } from 'react-router'
import '../index.css'

export default function Layout() {
  return (
    <div className="app">
      <nav className="nav" aria-label="Navegação principal">
        <NavLink to="/" end>
          Agora
        </NavLink>
        <NavLink to="/novo">Novo</NavLink>
        <NavLink to="/items">Todos</NavLink>
      </nav>
      <main className="conteudo">
        <Outlet />
      </main>
    </div>
  )
}
