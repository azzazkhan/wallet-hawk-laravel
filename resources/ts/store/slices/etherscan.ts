import { createAsyncThunk, createSlice, PayloadAction } from '@reduxjs/toolkit';
import { AxiosResponse } from 'axios';
import { RootState } from 'store';
import { Direction, Transaction } from 'types/etherscan';
import { transactionSorter } from 'utils';

interface State {
    filters: {
        start: Nullable<number>;
        end: Nullable<number>;
        direction: Nullable<Direction>;
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
        direction: null
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
        setStartDate(state, action: PayloadAction<Nullable<number>>) {
            state.filters.start = action.payload;
        },
        setEndDate(state, action: PayloadAction<Nullable<number>>) {
            state.filters.end = action.payload;
        },
        setDirection(state, action: PayloadAction<Nullable<Direction>>) {
            state.filters.direction = action.payload;
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
            const itemCount = state.items.length;

            if (action.payload.type === 'pagination') {
                state.page += 1;
                state.items.push(...action.payload.data);
            } else {
                state.items = action.payload.data;
            }

            state.filtered = state.items.sort(transactionSorter);
            state.canPaginate = state.items.length > itemCount;
        });
        builder.addCase(fetchTransactions.rejected, (state) => {
            state.status = 'error';
        });
    }
});

export const { setStartDate, setEndDate, setDirection, resetFilters, addItems, setItems } =
    etherscanSlice.actions;

export default etherscanSlice.reducer;
