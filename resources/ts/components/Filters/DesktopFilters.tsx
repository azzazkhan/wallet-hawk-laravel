/* eslint-disable jsx-a11y/anchor-is-valid */
import classnames from 'classnames';
import React, { FC, useState } from 'react';

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
    const options: SelectField<Direction> = [
        { label: 'Both', value: 'both', selected: true },
        { label: 'Incoming', value: 'in' },
        { label: 'Outgoing', value: 'out' }
    ];

    return (
        <div className="flex items-center space-x-2">
            <label htmlFor="direction">Direction</label>

            <select
                name="direction"
                id="direction"
                className="h-10 text-sm bg-white border border-gray-200 rounded-md"
            >
                {options.map(({ label, value, selected = false }, index) => {
                    return (
                        <option value={value} key={index} selected={selected}>
                            {label}
                        </option>
                    );
                })}
            </select>
        </div>
    );
};

const StartDateFilter: FC = () => {
    return (
        <div className="flex items-center space-x-2">
            <label htmlFor="start" className="text-sm font-medium">
                Start
            </label>
            <input
                type="date"
                id="start"
                className="h-10 text-sm bg-white border border-gray-200 rounded-md"
            />
        </div>
    );
};

const EndDateFilter: FC = () => {
    return (
        <div className="flex items-center space-x-2">
            <label htmlFor="end" className="text-sm font-medium">
                End
            </label>
            <input
                type="date"
                id="end"
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
