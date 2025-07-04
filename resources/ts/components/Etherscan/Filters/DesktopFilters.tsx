/* eslint-disable jsx-a11y/label-has-associated-control */
/* eslint-disable jsx-a11y/anchor-is-valid */
import classnames from 'classnames';
import { Dropdown } from 'flowbite-react';
import { useAppDispatch, useAppSelector } from 'hooks';
import React, { ChangeEventHandler, FC, MouseEventHandler, useCallback, useMemo } from 'react';
import {
    setDirection,
    setStartDate,
    setEndDate,
    filterItems,
    resetFilters
} from 'store/slices/etherscan';

declare type Direction = 'in' | 'out' | 'both';
declare type Token = 'etherscan' | 'opensea';

const SchemaSelection: FC = () => {
    const params = useMemo(() => new URLSearchParams(window.location.search), []);

    const handleClick = (scheme: Token) => {
        return (): void => {
            const current_scheme = (params.get('schema') || '').toLowerCase() as Nullable<'erc20'>;
            const query: Record<string, string | null> = {};

            if (scheme === 'etherscan' && current_scheme === 'erc20') return;
            if (scheme === 'opensea' && current_scheme !== 'erc20') return;

            query.schema = scheme === 'etherscan' ? 'erc20' : null;
            query.address = params.get('address');

            const query_string = Object.entries(query)
                .map(([key, value]) => {
                    return value ? `${key}=${value}` : null;
                })
                .filter((query) => Boolean(query))
                .join('&');

            window.location.href = `${window.location.pathname}?${query_string}`;
        };
    };

    return (
        <Dropdown label="Token" inline>
            <Dropdown.Item onClick={handleClick('opensea')}>ERC1155 / ERC721</Dropdown.Item>
            <Dropdown.Item onClick={handleClick('etherscan')}>ERC20</Dropdown.Item>
        </Dropdown>
    );
};

const DirectionSelector: FC = () => {
    const dispatch = useAppDispatch();
    const value = useAppSelector((state) => state.etherscan.filters.direction);

    const options: SelectField<Direction> = [
        { label: 'Both', value: 'both', selected: true },
        { label: 'Incoming', value: 'in' },
        { label: 'Outgoing', value: 'out' }
    ];

    const handleChange: ChangeEventHandler<HTMLSelectElement> = useCallback(
        (event) => {
            const { value } = event.target;

            dispatch(setDirection(value === 'in' || value === 'out' ? value : null));
        },
        [dispatch]
    );

    return (
        <div className="flex items-center space-x-2">
            <label htmlFor="direction">Direction</label>

            <select
                name="direction"
                id="direction"
                onChange={handleChange}
                className="h-10 text-sm bg-white border border-gray-200 rounded-md"
                defaultValue={value || 'both'}
            >
                {options.map(({ label, value }, index) => {
                    return (
                        <option value={value} key={index}>
                            {label}
                        </option>
                    );
                })}
            </select>
        </div>
    );
};

const StartDateFilter: FC = () => {
    const dispatch = useAppDispatch();
    const value = useAppSelector((state) => state.etherscan.filters.start);

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
    const value = useAppSelector((state) => state.etherscan.filters.end);

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
    const status = useAppSelector((state) => state.etherscan.status);
    const items = useAppSelector((state) => state.etherscan.items.length);
    const filtered = useAppSelector((state) => state.etherscan.filters.applied);

    const filterHandler: MouseEventHandler<HTMLButtonElement> = (event) => {
        event.preventDefault();

        if (status === 'loading' || items === 0) return;

        dispatch(filterItems());
    };

    const resetHandler: MouseEventHandler<HTMLButtonElement> = (event) => {
        event.preventDefault();

        if (!filtered || status === 'loading') return;

        dispatch(resetFilters());
    };

    return (
        <div className="flex items-center space-x-2 ml-auto">
            <button
                type="button"
                onClick={resetHandler}
                className={classnames(
                    'inline-block h-10 px-6 text-white transition-colors bg-gray-400 rounded-md hover:bg-gray-500',
                    {
                        'cursor-wait pointer-events-none opacity-60': !filtered
                    }
                )}
                disabled={!filtered}
            >
                Reset
            </button>
            <button
                type="button"
                onClick={filterHandler}
                className={classnames(
                    'inline-block h-10 px-6 text-white transition-colors bg-blue-500 rounded-md hover:bg-blue-600',
                    {
                        'cursor-wait pointer-events-none opacity-60':
                            status === 'loading' || items === 0
                    }
                )}
                disabled={status === 'loading' || items === 0}
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
            <DirectionSelector />
            <StartDateFilter />
            <EndDateFilter />
            <div className="flex-1" />
            <ApplyFiltersButton />
        </div>
    );
};

export default DesktopFilters;
