/* eslint-disable jsx-a11y/label-has-associated-control */
/* eslint-disable jsx-a11y/anchor-is-valid */
import classnames from 'classnames';
import { useAppDispatch, useAppSelector } from 'hooks';
import React, { ChangeEventHandler, FC, MouseEventHandler, useCallback, useMemo } from 'react';
import { setStartDate, setEndDate, fetchEvents } from 'store/slices/opensea';

const SchemaSelection: FC = () => {
    const params = useMemo(() => new URLSearchParams(window.location.search), []);

    return (
        <div className="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md">
            <a
                href="#"
                className="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none"
            >
                ERC 1155 / ERC 721
            </a>
            <a
                href={`/transactions?schema=ERC20&address=${params.get('address')}`}
                className="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white"
            >
                ERC20
            </a>
        </div>
    );
};

const StartDateFilter: FC = () => {
    const dispatch = useAppDispatch();
    const value = useAppSelector((state) => state.opensea.filters.start);

    const handleChange: ChangeEventHandler<HTMLInputElement> = useCallback(
        (event) => {
            dispatch(setStartDate(event.target.value));
        },
        [dispatch]
    );

    return (
        <div className="flex items-center space-x-2">
            <label htmlFor="start" className="text-sm font-medium">
                Start
            </label>
            <input
                type="date"
                id="start"
                onChange={handleChange}
                value={value || ''}
                className="h-10 text-sm bg-white border border-gray-200 rounded-md"
            />
        </div>
    );
};

const EndDateFilter: FC = () => {
    const dispatch = useAppDispatch();
    const value = useAppSelector((state) => state.opensea.filters.end);

    const handleChange: ChangeEventHandler<HTMLInputElement> = useCallback(
        (event) => {
            dispatch(setEndDate(event.target.value));
        },
        [dispatch]
    );

    return (
        <div className="flex items-center space-x-2">
            <label htmlFor="end" className="text-sm font-medium">
                End
            </label>
            <input
                type="date"
                id="end"
                onChange={handleChange}
                value={value || ''}
                className="h-10 text-sm bg-white border border-gray-200 rounded-md"
            />
        </div>
    );
};

const ApplyFiltersButton: FC = () => {
    const dispatch = useAppDispatch();
    const params = useMemo(() => new URLSearchParams(window.location.search), []);

    const status = useAppSelector((state) => state.opensea.status);
    const items = useAppSelector((state) => state.opensea.items.length);
    const filtered = useAppSelector((state) => state.opensea.filters.applied);

    const filterHandler: MouseEventHandler<HTMLButtonElement> = (event) => {
        event.preventDefault();

        if (status === 'loading' || items === 0) return;

        dispatch(fetchEvents({ address: params.get('address') || '', type: 'filter' }));
    };

    const resetHandler: MouseEventHandler<HTMLButtonElement> = (event) => {
        event.preventDefault();

        if (!filtered || status === 'loading') return;

        dispatch(fetchEvents({ address: params.get('address') || '', type: 'reset' }));
    };

    return (
        <div className="flex items-center ml-auto space-x-2">
            <button
                type="button"
                onClick={resetHandler}
                className={classnames(
                    'inline-block h-10 px-6 text-white transition-colors bg-gray-400 rounded-md hover:bg-gray-500',
                    {
                        'cursor-wait pointer-events-none opacity-60':
                            !filtered || status === 'loading'
                    }
                )}
                disabled={!filtered || status === 'loading'}
            >
                Reset
            </button>
            <button
                type="button"
                onClick={filterHandler}
                className={classnames(
                    'inline-block h-10 px-6 text-white transition-colors bg-blue-500 rounded-md hover:bg-blue-600',
                    { 'cursor-wait pointer-events-none opacity-60': status === 'loading' }
                )}
                disabled={status === 'loading'}
            >
                Apply
            </button>
        </div>
    );
};

const DesktopFilters: FC = () => {
    return (
        <div className="sticky z-50 items-center hidden h-16 px-5 space-x-6 bg-white rounded-lg shadow top-4 md:flex">
            <SchemaSelection />
            <StartDateFilter />
            <EndDateFilter />
            <div className="flex-1" />
            <ApplyFiltersButton />
        </div>
    );
};

export default DesktopFilters;
