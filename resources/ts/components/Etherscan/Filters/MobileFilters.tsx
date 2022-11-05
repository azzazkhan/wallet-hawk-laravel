import { Button, Dropdown, Label, Modal, Select, TextInput } from 'flowbite-react';
import { useAppDispatch, useAppSelector } from 'hooks';
import React, { ChangeEventHandler, FC, MouseEvent, MouseEventHandler, useMemo } from 'react';
import {
    filterItems,
    resetFilters,
    setDirection,
    setEndDate,
    setStartDate,
    toggleFilterModal
} from 'store/slices/etherscan';

declare type Token = 'etherscan' | 'opensea';
declare type Direction = 'in' | 'out' | 'both';

const DirectionSelectionField: FC = () => {
    const dispatch = useAppDispatch();
    const value = useAppSelector((state) => state.etherscan.filters.direction);

    const options: SelectField<Direction> = [
        { label: 'Both', value: 'both', selected: true },
        { label: 'Incoming', value: 'in' },
        { label: 'Outgoing', value: 'out' }
    ];

    const handleChange: ChangeEventHandler<HTMLSelectElement> = (event) => {
        const { value } = event.target;

        dispatch(setDirection(value === 'in' || value === 'out' ? value : null));
    };

    return (
        <div>
            <Label htmlFor="direction" value="Direction" />
            <Select id="direction" onChange={handleChange} defaultValue={value || 'both'}>
                {options.map(({ label, value }, index) => {
                    return (
                        <option value={value} key={index}>
                            {label}
                        </option>
                    );
                })}
            </Select>
        </div>
    );
};

const StartDateSelectionField: FC = () => {
    const dispatch = useAppDispatch();
    const value = useAppSelector((state) => state.etherscan.filters.start);

    const handleChange: ChangeEventHandler<HTMLInputElement> = (event) => {
        dispatch(setStartDate(event.target.value));
    };

    return (
        <div>
            <Label htmlFor="start_date" value="Start Date" />
            <TextInput type="date" id="start_date" onChange={handleChange} value={value || ''} />
        </div>
    );
};

const EndDateSelectionField: FC = () => {
    const dispatch = useAppDispatch();
    const value = useAppSelector((state) => state.etherscan.filters.end);

    const handleChange: ChangeEventHandler<HTMLInputElement> = (event) => {
        dispatch(setEndDate(event.target.value));
    };

    return (
        <div>
            <Label htmlFor="end_date" value="End Date" />
            <TextInput type="date" id="end_date" onChange={handleChange} value={value || ''} />
        </div>
    );
};

const ModalFooter: FC = () => {
    const dispatch = useAppDispatch();
    const status = useAppSelector((state) => state.etherscan.status);
    const items = useAppSelector((state) => state.etherscan.items.length);

    const filterHandler: MouseEventHandler<HTMLButtonElement> = (event) => {
        event.preventDefault();

        if (status === 'loading' || items === 0) return;

        dispatch(filterItems());
        dispatch(toggleFilterModal(false));
    };

    const handleClose = (event?: MouseEvent) => {
        event?.preventDefault();
        dispatch(toggleFilterModal(false));
    };

    return (
        <Modal.Footer>
            <Button onClick={filterHandler}>Apply</Button>
            <Button color="gray" onClick={handleClose}>
                Cancel
            </Button>
        </Modal.Footer>
    );
};

const FilterModal: FC = () => {
    const dispatch = useAppDispatch();
    const visible = useAppSelector((state) => state.etherscan.filters.opened);

    const handleClose = (event?: MouseEvent) => {
        event?.preventDefault();
        dispatch(toggleFilterModal(false));
    };

    return (
        <Modal show={visible} onClose={handleClose}>
            <Modal.Header>Filter Events</Modal.Header>
            <Modal.Body>
                <div className="flex flex-col gap-4">
                    <DirectionSelectionField />
                    <StartDateSelectionField />
                    <EndDateSelectionField />
                </div>
            </Modal.Body>
            <ModalFooter />
        </Modal>
    );
};

const MobileFilters: FC = () => {
    const dispatch = useAppDispatch();
    const params = useMemo(() => new URLSearchParams(window.location.search), []);
    const filtered = useAppSelector((state) => state.etherscan.filters.applied);

    const handleSchemaSelection = (scheme: Token) => {
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

    const handleClick: MouseEventHandler<HTMLButtonElement> = (event) => {
        event.preventDefault();

        dispatch(toggleFilterModal(true));
    };

    const handleReset: MouseEventHandler = (event) => {
        event.preventDefault();

        dispatch(resetFilters());
    };

    return (
        <div className="flex items-center justify-between mb-4 md:hidden">
            <Dropdown label="Token" color="gray">
                <Dropdown.Item onClick={handleSchemaSelection('opensea')}>
                    ERC1155 / ERC721
                </Dropdown.Item>
                <Dropdown.Item onClick={handleSchemaSelection('etherscan')}>ERC20</Dropdown.Item>
            </Dropdown>
            <div className="flex justify-end space-x-2">
                {filtered && (
                    <Button color="dark" onClick={handleReset}>
                        Reset
                    </Button>
                )}
                <button
                    type="button"
                    onClick={handleClick}
                    className="h-10 px-6 text-sm font-medium text-white transition-colors bg-blue-500 rounded-md cursor-pointer hover:bg-blue-600"
                >
                    <i className="inline-block mr-1 text-xs fas fa-filter" aria-hidden="true" />
                    Filters
                </button>
            </div>

            <FilterModal />
        </div>
    );
};

export default MobileFilters;
