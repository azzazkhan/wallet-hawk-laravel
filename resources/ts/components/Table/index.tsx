import { AxiosError } from 'axios';
import React, { FC, ReactNode, useEffect } from 'react';

const Table: FC<{ children?: ReactNode }> = ({ children }) => {
    const columns: string[] = ['Item', 'Direction', 'Quantity', 'From', 'To', 'Txn Fee', 'Time'];

    useEffect(() => {
        axios
            .get('etherscan')
            .then((response) => {
                console.log(response.data);
            })
            .catch((error: AxiosError) => {
                console.log(error.message);
            });
    }, []);

    return (
        <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table className="w-full text-sm text-left text-gray-500">
                <thead className="text-gray-700 uppercase bg-gray-50">
                    <tr>
                        {columns.map((label, index) => {
                            return (
                                <th scope="col" className="px-6 py-3" key={index}>
                                    {label}
                                </th>
                            );
                        })}
                    </tr>
                </thead>
                <tbody>{children}</tbody>
            </table>
        </div>
    );
};

export default Table;
