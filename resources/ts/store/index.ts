import { configureStore } from '@reduxjs/toolkit';
import { etherscan } from './slices';

const store = configureStore({
    reducer: {
        etherscan
    }
});

export default store;
