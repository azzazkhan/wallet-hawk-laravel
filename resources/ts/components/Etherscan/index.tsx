import React, { FC, MouseEventHandler, useEffect, useMemo } from 'react';
import { useAppDispatch, useAppSelector } from 'hooks';
import { fetchTransactions } from 'store/slices/etherscan';
import classnames from 'classnames';
import Filters from './Filters';
import Table from './Table';

const Etherscan: FC = () => {
    const dispatch = useAppDispatch();
    const params = useMemo(() => new URLSearchParams(window.location.search), []);

    const transactions = useAppSelector((state) => state.etherscan.filtered);
    const status = useAppSelector((state) => state.etherscan.status);
    const canPaginate = useAppSelector((state) => state.etherscan.canPaginate);

    const handlePagination: MouseEventHandler<HTMLButtonElement> = (event) => {
        event.preventDefault();
        if (status === 'loading') return;

        dispatch(fetchTransactions({ address: params.get('address') || '', type: 'pagination' }));
    };

    useEffect(() => {
        if (status === 'loading') return;

        dispatch(fetchTransactions({ address: params.get('address') || '', type: 'initial' }));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return (
        <div className="mt-10 space-y-4">
            <Filters />
            <Table transactions={transactions} />

            {canPaginate && (
                <button
                    type="button"
                    className={classnames(
                        'flex items-center space-x-2 h-10 px-5 mx-auto my-10 font-medium text-white transition-all bg-blue-500 rounded-md cursor-pointer hover:bg-blue-700',
                        {
                            'cursor-wait pointer-events-none opacity-60': status === 'loading'
                        }
                    )}
                    disabled={status === 'loading'}
                    onClick={handlePagination}
                >
                    {status === 'loading' && <i className="text-sm fa-solid fa-sync fa-spin" />}
                    <span>{status === 'loading' ? 'Loading' : 'Load More'}</span>
                </button>
            )}
        </div>
    );
};

export default Etherscan;
