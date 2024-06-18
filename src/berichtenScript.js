import BerichtenOverview from "./views/BerichtenOverview.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(BerichtenOverview);
new VueApp().$mount("#berichten");
