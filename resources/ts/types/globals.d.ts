import { AxiosError, AxiosInstance } from 'axios';

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    const axios: AxiosInstance;

    // General types
    type Nullable<T = unknown> = T | null;
    type Maybe<T = unknown> = Nullable<T>;

    // General resource model types
    type APIRecord<T = unknown> = { id: number; created_at: string; updated_at: string } & T;
    type DeletableApiRecord<T = unknown> = APIRecord<T> & { deleted_at: string };

    // Network related types
    type AsyncState = 'idle' | 'loading' | 'success' | 'error';
    type APIResponse<D = unknown> = { success: true; status: number; data: D };
    type APIError = AxiosError<{
        success: false;
        status: number;
        message: Nullable<string>;
    }>;
    type SelectField<T extends string = string> = Array<{
        label: string;
        value: T;
        selected?: boolean;
    }>;
}

export {};
