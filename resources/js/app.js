import './bootstrap';
import '../css/app.css';
import '../scss/bb.scss'

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/inertia-vue3';
import {InertiaProgress} from '@inertiajs/progress';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import Dialog from 'primevue/dialog';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Toast from 'primevue/toast';
import Tree from 'primevue/tree';
import TreeSelect from "primevue/treeselect";
import 'primevue/resources/primevue.min.css';
import 'primevue/resources/themes/mdc-light-indigo/theme.css'
import '/node_modules/primeflex/primeflex.css'
import 'primeicons/primeicons.css';
import DataTable from "primevue/datatable";
import Column from 'primevue/column';
import Button from "primevue/button";
import ColumnGroup from 'primevue/columngroup';     //optional for column grouping
import Row from 'primevue/row';                     //optional for row
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';
import {library} from '@fortawesome/fontawesome-svg-core'
import {faAnglesRight} from '@fortawesome/free-solid-svg-icons'
library.add(faAnglesRight)

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({el, app, props, plugin}) {
        return createApp({render: () => h(app, props)})
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(PrimeVue)
            .use(ToastService)
            .use(ConfirmationService)
            .component('Dialog', Dialog)
            .component('DataTable', DataTable)
            .component('Column', Column)
            .component('ColumnGroup', ColumnGroup)
            .component('Row', Row)
            .component('Tree', Tree)
            .component('Button', Button)
            .component('Toast', Toast)
            .component('TreeSelect', TreeSelect)
            .mount(el);
    },
});

InertiaProgress.init({color: '#4B5563'});
