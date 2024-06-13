import SamenwerkingOverview from "./views/SamenwerkingOverview.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(SamenwerkingOverview);
new VueApp().$mount("#content");
