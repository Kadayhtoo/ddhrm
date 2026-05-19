import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';

export default createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: { mdi },
    },
    theme: {
        defaultTheme: 'ddhrmLight',
        themes: {
            ddhrmLight: {
                dark: false,
                colors: {
                    primary: '#696cff',
                    secondary: '#8592a3',
                    accent: '#03c3ec',
                    success: '#71dd37',
                    warning: '#ffab00',
                    error: '#ff3e1d',
                    surface: '#f5f5f9',
                    background: '#f5f5f9',
                },
            },
        },
    },
});
