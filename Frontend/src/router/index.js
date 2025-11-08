import { createRouter, createWebHistory } from "vue-router";

import UsuariosView from "../views/UsuariosView.vue";
import CitasView from "../views/CitasView.vue";
import TiposCitasView from "../views/TiposCitasView.vue";
import IncidenciasView from "../views/IncidenciasView.vue";
import ExpedientesView from "../views/ExpedientesView.vue";
import DocumentosView from "../views/DocumentosView.vue";
import TiposDocumentosView from "../views/TiposDocumentosView.vue";
import PerfilesView from "../views/PerfilesView.vue";
import NotificacionesView from "../views/NotificacionesView.vue";
import LoginView from "../views/LoginView.vue";
import DashboardView from "../views/DashboardView.vue";

import MainLayout from "../layouts/MainLayout.vue";
const routes = [
  { path: "/login", component: LoginView },
  {
    path: "/",
    component: MainLayout,
    children: [
      { path: "", redirect: "/dashboard" },
      { path: "dashboard", component: DashboardView },
      { path: "usuarios", component: UsuariosView },
      { path: "citas", component: CitasView },
      { path: "tipos-citas", component: TiposCitasView },
      { path: "incidencias", component: IncidenciasView },
      { path: "expedientes", component: ExpedientesView },
      { path: "documentos", component: DocumentosView },
      { path: "tipos-documentos", component: TiposDocumentosView },
      { path: "perfiles", component: PerfilesView },
      { path: "notificaciones", component: NotificacionesView },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
