declare type Direction = 'in' | 'out';
declare type Schema = 'erc1155' | 'erc721';
export declare type EventType =
    | 'created'
    | 'successful'
    | 'cancelled'
    | 'bid_entered'
    | 'bid_withdrawn'
    | 'transfer'
    | 'offer_entered'
    | 'approve';

interface Account {
    user: {
        username: string;
    };
    profile_img_url: string;
    address: string;
    config: string;
}

export interface Event {
    name: Nullable<string>;
    image: Nullable<string>;
    animation: Nullable<string>;
    direction: Nullable<Direction>;
    token_id: Nullable<string>;
    asset_id: Nullable<number>;
    event_id: number;
    from: Nullable<Account>;
    to: Nullable<Account>;
    contract_address: Nullable<string>;
    from_account: Nullable<Account>;
    to_account: Nullable<Account>;
    seller_account: Nullable<Account>;
    winner_account: Nullable<Account>;
    owner_account: Nullable<Account>;
    schema: Nullable<Schema>;
    event_type: EventType;
    value: Nullable<number>;
    timestamp: string;
}
