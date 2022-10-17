/* eslint-disable prefer-destructuring */
import { createAsyncThunk, createSlice, PayloadAction } from '@reduxjs/toolkit';
import { AxiosResponse } from 'axios';
import moment from 'moment';
import { RootState } from 'store';
import { Direction, Transaction } from 'types/etherscan';
import { transactionSorter } from 'utils';

const ITEMS_PER_PAGE = 100;

interface State {
    filters: {
        start: Nullable<string>;
        end: Nullable<string>;
        direction: Nullable<Direction | 'both'>;
        applied: boolean;
    };
    canPaginate: boolean;
    page: number;
    status: AsyncState;
    items: Transaction[];
    filtered: Transaction[];
}

const initialState: State = {
    filters: {
        start: null,
        end: null,
        direction: 'both',
        applied: false
    },
    canPaginate: true,
    page: 2,
    status: 'idle',
    items: [],
    filtered: []
};

interface ThunkProps {
    address: string;
    type: 'initial' | 'pagination';
}
export const fetchTransactions = createAsyncThunk(
    'etherscan/fetch-transactions',
    async ({ address, type }: ThunkProps, { getState }) => {
        let url = `/etherscan?address=${address}`;

        if (type === 'pagination') {
            const state = getState() as RootState;
            url = `${url}&page=${state.etherscan.page}`;
        }

        console.log({ url });

        const response: AxiosResponse<APIResponse<Transaction[]>> = await axios.get(url);

        return { type, data: response.data.data };
    }
);

const etherscanSlice = createSlice({
    name: 'etherscan',
    initialState,
    reducers: {
        setStartDate(state, action: PayloadAction<Nullable<string>>) {
            state.filters.start = action.payload;
        },
        setEndDate(state, action: PayloadAction<Nullable<string>>) {
            state.filters.end = action.payload;
        },
        setDirection(state, action: PayloadAction<Nullable<Direction>>) {
            state.filters.direction = action.payload;
        },
        filterItems(state) {
            const { direction } = state.filters;
            const start = state.filters.start ? moment(state.filters.start).unix() : 0;
            const end = state.filters.end ? moment(state.filters.end).unix() : 0;
            let filtered = [...state.items];

            if (direction)
                filtered = filtered.filter((transaction) => transaction.direction === direction);

            if (start || end) {
                const startDate = start < end ? start : end;
                const endDate = end > start ? end : start;

                filtered = filtered.filter((transaction) => {
                    if (startDate && transaction.timestamp < startDate) return false;

                    if (endDate && transaction.timestamp > endDate) return false;

                    return true;
                });
            }

            state.filtered = [...filtered].sort(transactionSorter);
            state.filters.applied = true;
        },
        resetFilters(state) {
            state.filters = { ...initialState.filters };
            state.filtered = state.items.sort(transactionSorter);
        },
        setItems(state, action: PayloadAction<Transaction[]>) {
            state.items = action.payload;
            state.filtered = [...state.items].sort(transactionSorter);
        },
        addItems(state, action: PayloadAction<Transaction[]>) {
            state.items = [...state.items, ...action.payload];
            state.filtered = [...state.items].sort(transactionSorter);
        }
    },
    extraReducers(builder) {
        builder.addCase(fetchTransactions.pending, (state) => {
            state.status = 'loading';
        });
        builder.addCase(fetchTransactions.fulfilled, (state, action) => {
            state.status = 'success';
            state.canPaginate = action.payload.data.length >= ITEMS_PER_PAGE;

            if (action.payload.type === 'pagination') {
                state.page += 1;
                state.items.push(...action.payload.data);
            } else {
                state.items = action.payload.data;
            }

            state.filtered = state.items.sort(transactionSorter);
        });
        builder.addCase(fetchTransactions.rejected, (state) => {
            state.status = 'error';
        });
    }
});

export const {
    setStartDate,
    setEndDate,
    setDirection,
    filterItems,
    resetFilters,
    addItems,
    setItems
} = etherscanSlice.actions;

export default etherscanSlice.reducer;
