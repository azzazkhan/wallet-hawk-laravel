const params = [
    { name: 'lorem-ipsum', age: 21 },
    { type: 'human' },
    { dob: '22-04-2012', dod: '23-05-2058' }
];

const result = params
    .map((params) => {
        return Object.entries(params)
            .map(([key, value]) => `${key}=${value}`)
            .join('&');
    })
    .join('&');

console.log(result);
