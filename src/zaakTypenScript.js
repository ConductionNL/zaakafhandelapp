import ZaakTypenOverview from "./views/ZaakTypenOverview.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(ZaakTypenOverview);
new VueApp().$mount("#zaakTypen");
