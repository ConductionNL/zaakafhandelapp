import ZakenOverview from "./views/ZakenOverview.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(ZakenOverview);
new VueApp().$mount("#zaken");
