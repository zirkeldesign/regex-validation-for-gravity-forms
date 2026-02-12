<?php

declare(strict_types=1);

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
