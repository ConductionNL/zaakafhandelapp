declare module '@nextcloud/vue' {
    import { DefineComponent } from 'vue'

    // Define basic props type that can be extended if needed
    interface BaseProps {
        [key: string]: any
    }

    export const NcAppContent: DefineComponent<BaseProps>
    export const NcEmptyContent: DefineComponent<{
        name?: string
        description?: string
    }>
    export const NcButton: DefineComponent<{
        type?: 'primary' | 'secondary' | 'success' | 'warning' | 'error'
        [key: string]: any
    }>
}
