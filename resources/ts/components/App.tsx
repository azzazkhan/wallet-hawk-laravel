import { AxiosError, AxiosResponse } from 'axios';
import { useAppSelector } from 'hooks';
import React, { FC, MouseEventHandler, useEffect, useMemo, useState } from 'react';
import { useDispatch } from 'react-redux';
import { addItems, setItems } from 'store/slices/etherscan';
import { Transaction } from 'types/etherscan';
import classnames from 'classnames';
import Filters from './Filters';
import Table from './Table';

const App: FC = () => {
    const dispatch = useDispatch();
    const params = useMemo(() => new URLSearchParams(window.location.search), []);
    const [loading, setLoading] = useState(false);
    const [cursor, setCursor] = useState(2);
    const [canPaginate, setCanPaginate] = useState(true);
    const PAGE_SIZE = 20;

    const transactions = useAppSelector((state) => state.etherscan.items);

    const handlePagination: MouseEventHandler<HTMLButtonElement> = (event) => {
        event.preventDefault();
        if (!canPaginate || loading) return;

        setLoading(true);

        axios
            .get(`etherscan?address=${params.get('wallet')}&page=${cursor}`)
            .then((response: AxiosResponse<APIResponse<Transaction[]>>) => {
                dispatch(addItems(response.data.data));
                setCanPaginate(response.data.data.length >= PAGE_SIZE);
                setCursor((count) => count + 1);
            })
            .catch((error: AxiosError) => {
                console.log(error.message);
            })
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        setLoading(true);

        axios
            .get(`etherscan?address=${params.get('wallet')}`)
            .then((response: AxiosResponse<APIResponse<Transaction[]>>) => {
                dispatch(setItems(response.data.data));
                setCanPaginate(response.data.data.length >= PAGE_SIZE);
            })
            .catch((error: AxiosError) => {
                console.log(error.message);
            })
            .finally(() => setLoading(false));
    }, [params, dispatch]);

    return (
        <div className="mt-10 space-y-4 ">
            <Filters />
            <Table transactions={transactions} />

            {canPaginate && (
                <button
                    type="button"
                    className={classnames(
                        'flex items-center h-10 px-5 mx-auto my-10 font-medium text-white transition-all bg-blue-500 rounded-md cursor-pointer hover:bg-blue-700',
                        {
                            'cursor-wait pointer-events-none opacity-60': loading
                        }
                    )}
                    onClick={handlePagination}
                >
                    {loading ? 'Loading' : 'Load More'}
                </button>
            )}
        </div>
    );
};

export default App;
