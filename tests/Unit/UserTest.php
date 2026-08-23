<?php

describe('User Class', function (): void {

    it('is equal with the same properties', function (): void {
        expect(createUser(name: 'Ivan'))->toEqual(createUser(name: 'Ivan'));
    });

    it('is not equal when the properties are different', function (): void {
        expect(createUser(name: 'Ivan'))->not()->toEqual(createUser(name: 'Olga'));
    });
});
