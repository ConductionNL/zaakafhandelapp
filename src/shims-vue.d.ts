declare module '*.vue' {
    // Vue 3 has no default-exported `Vue` constructor; an SFC's default export
    // is a component *definition*. The Vue-2 shim (`import Vue from 'vue'` +
    // `export default Vue`) types every `.vue` import as the global
    // constructor, which is both wrong and — since `vue@3` ships no such
    // default export — unresolvable.
    import type { DefineComponent } from 'vue'

    const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, unknown>
    export default component
}
