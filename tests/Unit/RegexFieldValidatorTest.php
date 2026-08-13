<?php

declare(strict_types=1);

use ZirkelDesign\GFRegexValidation\CompoundFieldPresets;
use ZirkelDesign\GFRegexValidation\RegexFieldValidator;

describe('phpRegexToJs', function () {
    it('strips delimiters and flags from a PHP regex', function () {
        expect(RegexFieldValidator::phpRegexToJs('/^[\p{L}\s\'-]+$/u'))
            ->toBe('^[\p{L}\s\'-]+$');
    });

    it('handles different delimiters', function () {
        expect(RegexFieldValidator::phpRegexToJs('#^[a-z]+$#i'))
            ->toBe('^[a-z]+$');
    });

    it('handles regex without flags', function () {
        expect(RegexFieldValidator::phpRegexToJs('/^[0-9]+$/'))
            ->toBe('^[0-9]+$');
    });

    it('returns null for empty string', function () {
        expect(RegexFieldValidator::phpRegexToJs(''))->toBeNull();
    });

    it('returns null for single character', function () {
        expect(RegexFieldValidator::phpRegexToJs('/'))->toBeNull();
    });

    it('returns null for missing closing delimiter', function () {
        expect(RegexFieldValidator::phpRegexToJs('/^[a-z]+$'))->toBeNull();
    });

    it('preserves anchors in pattern', function () {
        expect(RegexFieldValidator::phpRegexToJs('/^test$/u'))
            ->toBe('^test$');
    });

    it('handles complex patterns with groups', function () {
        expect(RegexFieldValidator::phpRegexToJs('/^(\+?1)?[\s.-]?\(?[2-9]\d{2}\)?$/u'))
            ->toBe('^(\+?1)?[\s.-]?\(?[2-9]\d{2}\)?$');
    });
});

describe('isValidRegex', function () {
    it('returns true for valid regex patterns', function () {
        expect(RegexFieldValidator::isValidRegex('/^[\p{L}\s]+$/u'))->toBeTrue();
        expect(RegexFieldValidator::isValidRegex('/^[a-z]+$/i'))->toBeTrue();
        expect(RegexFieldValidator::isValidRegex('/^\d{5}$/'))->toBeTrue();
    });

    it('returns false for invalid regex patterns', function () {
        expect(RegexFieldValidator::isValidRegex('/^[invalid/'))->toBeFalse();
        expect(RegexFieldValidator::isValidRegex('not a regex'))->toBeFalse();
    });
});

describe('getPresets', function () {
    it('returns an array of presets', function () {
        $presets = RegexFieldValidator::getPresets();

        expect($presets)->toBeArray();
        expect($presets)->toHaveKeys(['name', 'email', 'phone_us', 'phone_international', 'alphanumeric', 'no_special_chars']);
    });

    it('has required keys in each preset', function () {
        $presets = RegexFieldValidator::getPresets();

        foreach ($presets as $key => $preset) {
            expect($preset)->toHaveKeys(['label', 'pattern', 'message'])
                ->and($preset['pattern'])->toBeString()
                ->and($preset['label'])->toBeString()
                ->and($preset['message'])->toBeString();
        }
    });

    it('has valid regex patterns in all presets', function () {
        $presets = RegexFieldValidator::getPresets();

        foreach ($presets as $key => $preset) {
            expect(RegexFieldValidator::isValidRegex($preset['pattern']))
                ->toBeTrue("Preset '{$key}' has an invalid regex pattern: {$preset['pattern']}");
        }
    });

    it('has convertible patterns for JS in all presets', function () {
        $presets = RegexFieldValidator::getPresets();

        foreach ($presets as $key => $preset) {
            expect(RegexFieldValidator::phpRegexToJs($preset['pattern']))
                ->not->toBeNull("Preset '{$key}' pattern cannot be converted to JS");
        }
    });
});

describe('preset pattern matching', function () {
    it('name preset matches valid names', function () {
        $presets = RegexFieldValidator::getPresets();
        $pattern = $presets['name']['pattern'];

        expect(preg_match($pattern, 'John Doe'))->toBe(1);
        expect(preg_match($pattern, "O'Brien"))->toBe(1);
        expect(preg_match($pattern, 'Mary-Jane'))->toBe(1);
    });

    it('name preset rejects invalid names', function () {
        $presets = RegexFieldValidator::getPresets();
        $pattern = $presets['name']['pattern'];

        expect(preg_match($pattern, 'John123'))->toBe(0);
        expect(preg_match($pattern, 'test@email'))->toBe(0);
    });

    it('alphanumeric preset matches unicode', function () {
        $presets = RegexFieldValidator::getPresets();
        $pattern = $presets['alphanumeric']['pattern'];

        expect(preg_match($pattern, 'abc123'))->toBe(1);
    });

    it('alphanumeric preset rejects special chars', function () {
        $presets = RegexFieldValidator::getPresets();
        $pattern = $presets['alphanumeric']['pattern'];

        expect(preg_match($pattern, 'abc 123'))->toBe(0);
        expect(preg_match($pattern, 'test!'))->toBe(0);
    });
});

// New tests for compound field presets and advanced mode
describe('CompoundFieldPresets', function () {
    describe('getCompoundPresets', function () {
        it('returns an array of compound presets', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();

            expect($presets)->toBeArray();
            expect(count($presets))->toBeGreaterThan(0);
        });

        it('has required keys in each compound preset', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();

            foreach ($presets as $key => $preset) {
                expect($preset)->toHaveKeys(['label', 'type', 'patterns'])
                    ->and($preset['label'])->toBeString()
                    ->and($preset['type'])->toBeString()
                    ->and($preset['patterns'])->toBeArray();
            }
        });

        it('has valid patterns in compound presets', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();

            foreach ($presets as $presetKey => $preset) {
                foreach ($preset['patterns'] as $inputNumber => $inputConfig) {
                    expect($inputConfig)->toHaveKeys(['pattern', 'message'])
                        ->and(RegexFieldValidator::isValidRegex($inputConfig['pattern']))
                        ->toBeTrue("Preset '{$presetKey}' input '{$inputNumber}' has invalid pattern");
                }
            }
        });

        it('includes name field presets', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $namePresets = array_filter($presets, fn ($p) => $p['type'] === 'name');

            expect(count($namePresets))->toBeGreaterThan(0);
        });

        it('includes address field presets', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $addressPresets = array_filter($presets, fn ($p) => $p['type'] === 'address');

            expect(count($addressPresets))->toBeGreaterThan(0);
        });
    });

    describe('getInputPresets', function () {
        it('returns an array of input presets', function () {
            $presets = CompoundFieldPresets::getInputPresets();

            expect($presets)->toBeArray();
            expect(count($presets))->toBeGreaterThan(0);
        });

        it('has required keys in each input preset', function () {
            $presets = CompoundFieldPresets::getInputPresets();

            foreach ($presets as $key => $preset) {
                expect($preset)->toHaveKeys(['label', 'pattern', 'message'])
                    ->and($preset['pattern'])->toBeString()
                    ->and($preset['label'])->toBeString()
                    ->and($preset['message'])->toBeString();
            }
        });

        it('has valid regex patterns', function () {
            $presets = CompoundFieldPresets::getInputPresets();

            foreach ($presets as $key => $preset) {
                expect(RegexFieldValidator::isValidRegex($preset['pattern']))
                    ->toBeTrue("Input preset '{$key}' has an invalid regex pattern");
            }
        });
    });

    describe('input structure methods', function () {
        it('returns name input structure', function () {
            $inputs = CompoundFieldPresets::getNameInputStructure();

            expect($inputs)->toBeArray()
                ->and($inputs)->toHaveKeys(['2', '3', '4', '6', '8']);
        });

        it('returns address input structure', function () {
            $inputs = CompoundFieldPresets::getAddressInputStructure();

            expect($inputs)->toBeArray()
                ->and($inputs)->toHaveKeys(['1', '2', '3', '4', '5', '6']);
        });
    });
});

describe('Compound preset pattern matching', function () {
    describe('US address preset', function () {
        it('matches valid US addresses', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['address_us']['patterns'];

            // Street address
            expect(preg_match($patterns['1']['pattern'], '123 Main St'))->toBe(1);
            expect(preg_match($patterns['1']['pattern'], '456 Oak Ave #2B'))->toBe(1);

            // City
            expect(preg_match($patterns['3']['pattern'], 'New York'))->toBe(1);
            expect(preg_match($patterns['3']['pattern'], "O'Fallon"))->toBe(1);

            // State
            expect(preg_match($patterns['4']['pattern'], 'CA'))->toBe(1);
            expect(preg_match($patterns['4']['pattern'], 'NY'))->toBe(1);

            // ZIP
            expect(preg_match($patterns['5']['pattern'], '12345'))->toBe(1);
            expect(preg_match($patterns['5']['pattern'], '12345-6789'))->toBe(1);
        });

        it('rejects invalid US addresses', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['address_us']['patterns'];

            // State should be 2 uppercase letters
            expect(preg_match($patterns['4']['pattern'], 'California'))->toBe(0);
            expect(preg_match($patterns['4']['pattern'], 'ca'))->toBe(0);

            // ZIP should be 5 or 5+4 digits
            expect(preg_match($patterns['5']['pattern'], '1234'))->toBe(0);
            expect(preg_match($patterns['5']['pattern'], '12345-'))->toBe(0);
        });
    });

    describe('German address preset', function () {
        it('matches valid German PLZ', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['address_german']['patterns'];

            expect(preg_match($patterns['5']['pattern'], '10115'))->toBe(1);
            expect(preg_match($patterns['5']['pattern'], '80331'))->toBe(1);
        });

        it('rejects invalid German PLZ', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['address_german']['patterns'];

            expect(preg_match($patterns['5']['pattern'], '1234'))->toBe(0);
            expect(preg_match($patterns['5']['pattern'], '123456'))->toBe(0);
        });
    });

    describe('Canadian address preset', function () {
        it('matches valid Canadian postal codes', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['address_canadian']['patterns'];

            expect(preg_match($patterns['5']['pattern'], 'K1A 0B1'))->toBe(1);
            expect(preg_match($patterns['5']['pattern'], 'K1A0B1'))->toBe(1); // Without space
            expect(preg_match($patterns['5']['pattern'], 'M5H 2N2'))->toBe(1);
        });

        it('rejects invalid Canadian postal codes', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['address_canadian']['patterns'];

            expect(preg_match($patterns['5']['pattern'], '12345'))->toBe(0);
            expect(preg_match($patterns['5']['pattern'], 'ABC123'))->toBe(0);
        });
    });

    describe('Name presets', function () {
        it('matches valid name inputs', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['name_standard']['patterns'];

            // First name
            expect(preg_match($patterns['3']['pattern'], 'John'))->toBe(1);
            expect(preg_match($patterns['3']['pattern'], "O'Brien"))->toBe(1);
            expect(preg_match($patterns['3']['pattern'], 'Mary-Jane'))->toBe(1);

            // Last name
            expect(preg_match($patterns['6']['pattern'], 'Smith'))->toBe(1);
            expect(preg_match($patterns['6']['pattern'], 'Van Der Berg'))->toBe(1);
        });

        it('matches valid name titles', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['name_with_title']['patterns'];

            // Prefix
            expect(preg_match($patterns['2']['pattern'], 'Mr.'))->toBe(1);
            expect(preg_match($patterns['2']['pattern'], 'Mrs'))->toBe(1);
            expect(preg_match($patterns['2']['pattern'], 'Dr.'))->toBe(1);
            expect(preg_match($patterns['2']['pattern'], 'Prof'))->toBe(1);
        });

        it('rejects invalid names', function () {
            $presets = CompoundFieldPresets::getCompoundPresets();
            $patterns = $presets['name_standard']['patterns'];

            expect(preg_match($patterns['3']['pattern'], 'John123'))->toBe(0);
            expect(preg_match($patterns['3']['pattern'], 'test@email'))->toBe(0);
        });
    });
});
