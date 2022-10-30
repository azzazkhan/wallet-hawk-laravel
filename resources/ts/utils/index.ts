/* eslint-disable camelcase */
import moment from 'moment';
import { Direction, Transaction } from 'types/etherscan';
import { Event } from 'types/opensea';

export const transactionSorter = (a: Transaction, b: Transaction): number => {
    if (a.timestamp > b.timestamp) return -1;

    if (b.timestamp > a.timestamp) return 1;

    return 0;
};

export const eventSorter = (a: Event, b: Event): number => {
    const a_timestamp = moment(a.timestamp).unix();
    const b_timestamp = moment(b.timestamp).unix();

    if (a_timestamp > b_timestamp) return -1;

    if (b_timestamp > a_timestamp) return 1;

    return 0;
};

declare type FilterFn = (
    transactions: Transaction[],
    filters: {
        direction: Nullable<Direction | 'both'>;
        start_date: Nullable<string>;
        end_date: Nullable<string>;
    }
) => Transaction[];
export const filterTransactions: FilterFn = (transactions, { start_date, end_date, direction }) => {
    let filtered = [...transactions];

    const start = start_date ? moment(start_date).unix() * 1000 : 0;
    const end = end_date ? moment(end_date).unix() * 1000 : 0;
    const startDate = start < end ? start : end;
    const endDate = end > start ? end : start;

    // console.log({ direction, start, end, startDate, endDate });

    if (direction && (direction === 'in' || direction === 'out'))
        filtered = filtered.filter((transaction) => transaction.direction === direction);

    if (startDate || endDate)
        filtered = filtered.filter((transaction) => {
            const timestamp = transaction.timestamp * 1000;

            if (startDate && !(timestamp >= startDate)) return false;

            if (endDate && !(timestamp <= endDate)) return false;

            return true;
        });

    return filtered;
};
