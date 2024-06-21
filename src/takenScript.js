import TakenOverview from "./views/TakenOverview.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(TakenOverview);
new VueApp().$mount("#taken");
