/* eslint-disable @typescript-eslint/no-use-before-define */
import { createAsyncThunk, createSlice, PayloadAction } from '@reduxjs/toolkit';
import { AxiosResponse } from 'axios';
import moment from 'moment';
import { RootState } from 'store';
import { Event } from 'types/opensea';
import { eventSorter } from 'utils';

interface State {
    filters: {
        start: Nullable<string>;
        end: Nullable<string>;
        type: Nullable<string>;
        applied: boolean;
    };
    cursor: Nullable<string>;
    status: AsyncState;
    items: Event[];
}

interface ThunkProps {
    address: string;
    type: 'initial' | 'pagination' | 'filter' | 'reset';
}
export const fetchEvents = createAsyncThunk(
    'opensea/fetch-events',
    async ({ address, type }: ThunkProps, { getState, dispatch }) => {
        const params: Record<string, string | number>[] = [{ address }];
        const state = (getState() as RootState).opensea;

        // If it is a filter query or pagination query then append the filter
        // params to url
        if (type === 'filter' || (type === 'pagination' && state.filters.applied)) {
            const { start, end, type } = state.filters;

            if (start) params.push({ start_date: moment(start).format('DD-MM-YYYY') });
            if (end) params.push({ end_date: moment(end).format('DD-MM-YYYY') });
            if (type) params.push({ type });
        }

        // If it is a pagination request and we have a cursor then append it
        if (type === 'pagination' && state.cursor) params.push({ cursor: state.cursor });

        const query = params
            .map((params) => {
                return Object.entries(params)
                    .map(([key, value]) => `${key}=${value}`)
                    .join('&');
            })
            .join('&');

        const response: AxiosResponse<APIResponse<{ events: Event[]; cursor: Nullable<string> }>> =
            await axios.get(`/opensea?${query}`);

        if (type === 'reset') dispatch(resetFilters());

        return { type, ...response.data.data };
    }
);

const initialState: State = {
    filters: {
        start: null,
        end: null,
        type: null,
        applied: false
    },
    cursor: null,
    status: 'idle',
    items: []
};

const openseaSlice = createSlice({
    name: 'opensea',
    initialState,
    reducers: {
        setStartDate(state, action: PayloadAction<Nullable<string>>) {
            state.filters.start = action.payload;
        },
        setEndDate(state, action: PayloadAction<Nullable<string>>) {
            state.filters.end = action.payload;
        },
        setEventType(state, action: PayloadAction<Nullable<string>>) {
            state.filters.type = action.payload;
        },
        resetFilters(state) {
            state.filters = { ...initialState.filters };
        }
    },
    extraReducers(builder) {
        builder.addCase(fetchEvents.pending, (state) => {
            state.status = 'loading';
        });
        builder.addCase(fetchEvents.fulfilled, (state, action) => {
            state.status = 'success';

            if (action.payload.type === 'pagination') state.items.push(...action.payload.events);
            else state.items = action.payload.events;

            state.cursor = action.payload.cursor;
            if (action.payload.type === 'filter') state.filters.applied = true;

            state.items = state.items.sort(eventSorter);
        });
        builder.addCase(fetchEvents.rejected, (state) => {
            state.status = 'error';
        });
    }
});

export const { setStartDate, setEndDate, setEventType, resetFilters } = openseaSlice.actions;

export default openseaSlice.reducer;
