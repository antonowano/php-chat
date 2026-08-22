<?php

use Antonowano\Chat\User;

describe('User', function (): void {

    it('is equal with the same properties', function (): void {
        expect(new User('Ivan'))->toEqual(new User('Ivan'));
    });

    it('is not equal when the properties are different', function (): void {
        expect(new User('Ivan'))->not()->toEqual(new User('Olga'));
    });
});
