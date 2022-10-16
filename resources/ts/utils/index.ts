import { Transaction } from 'types/etherscan';

export const transactionSorter = (a: Transaction, b: Transaction): number => {
    if (a.timestamp > b.timestamp) return -1;

    if (b.timestamp > a.timestamp) return 1;

    return 0;
};
