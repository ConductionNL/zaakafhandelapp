import DashboardView from "./views/DashboardView.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(DashboardView);
new VueApp().$mount("#dashboard");
