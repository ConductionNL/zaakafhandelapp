import ContactMomentenOverview from "./views/ContactMomentenOverview.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(ContactMomentenOverview);
new VueApp().$mount("#contactMomenten");
