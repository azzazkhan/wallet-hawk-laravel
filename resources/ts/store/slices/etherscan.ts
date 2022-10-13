import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { Direction, Transaction } from 'types/etherscan';

interface State {
    filters: {
        start: Nullable<number>;
        end: Nullable<number>;
        direction: Nullable<Direction>;
    };
    items: Transaction[];
}

const initialState: State = {
    filters: {
        start: null,
        end: null,
        direction: null
    },
    items: []
};

const etherscanSlice = createSlice({
    name: 'etherscan',
    initialState,
    reducers: {
        setStartDate(state, action: PayloadAction<number>) {
            state.filters.start = action.payload;
        },
        setEndDate(state, action: PayloadAction<number>) {
            state.filters.end = action.payload;
        },
        setDirection(state, action: PayloadAction<Direction>) {
            state.filters.direction = action.payload;
        },
        resetFilters(state) {
            state.filters = { ...initialState.filters };
        },
        setItems(state, action: PayloadAction<Transaction[]>) {
            state.items = action.payload;
        },
        addItems(state, action: PayloadAction<Transaction[]>) {
            state.items.push(...action.payload);
        },
        clearItems(state) {
            state.items = [];
        }
    }
});

export const {
    setStartDate,
    setEndDate,
    setDirection,
    resetFilters,
    addItems,
    setItems,
    clearItems
} = etherscanSlice.actions;

export default etherscanSlice.reducer;
