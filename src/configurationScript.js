import ZakenOverview from "./views/ConfigurationForm.vue";
import Vue from "vue";
Vue.mixin({ methods: { t, n } });

const VueApp = Vue.extend(ConfigurationForm);
new VueApp().$mount("#configuration");
