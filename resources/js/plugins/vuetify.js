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
                    primary: '#641C55',
                    secondary: '#8A3A78',
                    accent: '#B85AA3',
                    success: '#4CAF50',
                    warning: '#FB8C00',
                    error: '#E53935',
                    info: '#2196F3',
                    surface: '#FFFFFF',
                    background: '#F8F5F8',
                    'on-primary': '#FFFFFF',
                    'on-secondary': '#FFFFFF',
                    'on-background': '#1E1B1D',
                    'on-surface': '#1E1B1D',
                },
            },
            ddhrmDark: {
                dark: true,
                colors: {
                    primary: '#641C55',
                    secondary: '#9C4D8B',
                    accent: '#D17BC0',
                    success: '#66BB6A',
                    warning: '#FFA726',
                    error: '#EF5350',
                    info: '#42A5F5',
                    surface: '1D1A20',
                    background: '#121014',
                    'on-primary': '#FFFFFF',
                    'on-secondary': '#FFFFFF',
                    'on-background': '#F5F1F5',
                    'on-surface': '#F5F1F5',
                },
            },
        },
    },
});
