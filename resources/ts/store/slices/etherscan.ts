import { createSlice, PayloadAction } from '@reduxjs/toolkit';

declare type Direction = 'in' | 'out';

interface State {
    filters: {
        start: Nullable<number>;
        end: Nullable<number>;
        direction: Nullable<Direction>;
    };
}

const initialState: State = {
    filters: {
        start: null,
        end: null,
        direction: null
    }
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
        }
    }
});

export const { setStartDate, setEndDate, setDirection } = etherscanSlice.actions;

export default etherscanSlice.reducer;
