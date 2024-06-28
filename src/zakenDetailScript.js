import ZakenDetail from "./views/zaken/ZakenDetail.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(ZakenDetail);
new VueApp().$mount("#zakenDetail");
