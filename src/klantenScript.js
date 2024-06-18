import KlantenOverview from "./views/KlantenOverview.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(KlantenOverview);
new VueApp().$mount("#klanten");
