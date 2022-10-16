/* eslint-disable jsx-a11y/label-has-associated-control */
/* eslint-disable jsx-a11y/anchor-is-valid */
import classnames from 'classnames';
import { useAppDispatch, useAppSelector } from 'hooks';
import moment from 'moment';
import React, { ChangeEventHandler, FC, useCallback, useState } from 'react';
import { setDirection, setStartDate, setEndDate } from 'store/slices/etherscan';

declare type Direction = 'in' | 'out' | 'both';

const SchemaSelection: FC = () => {
    return (
        <div className="flex items-stretch h-10 overflow-hidden border border-gray-200 rounded-md">
            <a
                href="/transactions"
                className="flex items-center px-3 text-sm transition-colors hover:bg-blue-600 hover:text-white"
            >
                ERC 1155 / ERC 721
            </a>
            <a
                href="#"
                className="flex items-center px-3 text-sm text-gray-500 bg-gray-200 cursor-not-allowed pointer-events-none"
            >
                ERC20
            </a>
        </div>
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

            if (value === 'in' || value === 'out') dispatch(setDirection(value));
            dispatch(setDirection(null));
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
            dispatch(setStartDate(moment(event.target.value).unix()));
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
                value={value ? moment.unix(value).format('YYYY-MM-DD') : ''}
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
            dispatch(setEndDate(moment(event.target.value).unix()));
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
                value={value ? moment.unix(value).format('YYYY-MM-DD') : ''}
                className="h-10 text-sm bg-white border border-gray-200 rounded-md"
            />
        </div>
    );
};

const ApplyFiltersButton: FC = () => {
    const [loading, setLoading] = useState(false);

    return (
        <button
            type="button"
            className={classnames(
                'inline-block h-10 px-6 ml-auto text-white transition-colors bg-blue-500 rounded-md hover:bg-blue-600',
                { 'cursor-wait pointer-events-none opacity-60': loading }
            )}
        >
            {loading ? 'Filtering' : 'Apply'}
        </button>
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
