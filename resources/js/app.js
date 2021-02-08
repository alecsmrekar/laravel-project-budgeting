/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import VueRouter from 'vue-router'
import EditProjectModal from "./components/EditProjectModal";
import ProjectsComponent from "./components/ProjectsComponent";
import ProjectEditor from "./components/ProjectEditor";
import NewCostItem from "./components/CostItemModal";

window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
Vue.use(VueRouter)
Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('transaction-table', require('./components/TransactionsComponent.vue').default);
Vue.component('project-table', require('./components/ProjectsComponent.vue').default);
Vue.component('project-modal', require('./components/EditProjectModal.vue').default);
Vue.component('project-editor', require('./components/ProjectEditor.vue').default);
Vue.component('new-cost-item', require('./components/CostItemModal.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
/* https://www.telerik.com/blogs/how-to-emit-data-in-vue-beyond-the-vuejs-documentation */

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/edit_project_vue/:id',
            name: 'EditProjectModal',
            component: EditProjectModal
        },
        {
            path: '/project_list',
            name: 'ProjectList',
            component: ProjectsComponent
        },
        {
            path: '/project_editor/:id',
            name: 'ProjectEditor',
            component: ProjectEditor
        }
    ],
});

const app = new Vue({
    el: '#app',
    router,
});
