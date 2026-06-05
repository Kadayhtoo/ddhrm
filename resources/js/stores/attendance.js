import { defineStore } from 'pinia';
import axios from 'axios';

export const useAttendanceStore = defineStore('attendance', {
    state: () => ({
        today: null,
        widgets: {
            present: 0,
            absent: 0,
            late: 0,
            attendance_percentage: 0,
        },
        loadingToday: false,
        actionLoading: false,
    }),
    getters: {
        hasCheckedIn: (state) => Boolean(state.today?.clock_in_at),
        hasCheckedOut: (state) => Boolean(state.today?.clock_out_at),
    },
    actions: {
        async fetchToday() {
            this.loadingToday = true;
            try {
                const { data } = await axios.get('/api/attendance/today');
                this.today = data.data;
            } finally {
                this.loadingToday = false;
            }
        },
        async fetchWidgets() {
            const { data } = await axios.get('/api/attendance/reports/widgets');
            this.widgets = data.data ?? this.widgets;
        },
        async checkIn(notes = '') {
            this.actionLoading = true;
            try {
                const { data } = await axios.post('/api/attendance/check-in', { notes });
                this.today = data.data;
                await this.fetchWidgets();
            } finally {
                this.actionLoading = false;
            }
        },
        async checkOut(notes = '') {
            this.actionLoading = true;
            try {
                const { data } = await axios.post('/api/attendance/check-out', { notes });
                this.today = data.data;
                await this.fetchWidgets();
            } finally {
                this.actionLoading = false;
            }
        },
    },
});
