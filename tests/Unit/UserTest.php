<?php

describe('User', function (): void {

    it('is equal with the same properties', function (): void {
        expect(createUser('Ivan'))->toEqual(createUser('Ivan'));
    });

    it('is not equal when the properties are different', function (): void {
        expect(createUser('Ivan'))->not()->toEqual(createUser('Olga'));
    });
});
