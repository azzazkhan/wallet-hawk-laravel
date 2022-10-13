export declare type Direction = 'in' | 'out';

export interface Transaction {
    direction: Nullable<Direction>;
    hash: string;
    fee: number;
    from: string;
    to: string;
    name: string;
    quantity: number;
    timestamp: number;
}
