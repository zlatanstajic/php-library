<?php

use PHP_Library\Core\Arrangements\Email;

it('validates a well-formed address and returns it', function (string $address) {
    expect(Email::validate($address))->toBe($address);
})->with(['a@b.com', 'user.name@sub.domain.co.uk', 'first-last@example.org']);

it('rejects malformed addresses', function (?string $address) {
    expect(Email::validate($address))->toBeFalse();
})->with(['bad@', '@bad.com', 'no-at-sign', '', null, 'a@b']);

it('does not rely on a stale built-in provider blocklist', function () {
    expect(Email::validate('someone@mailinator.com'))->toBe('someone@mailinator.com');
});

it('accepts a custom blocklist, replacing the default', function () {
    expect(Email::validate('someone@blocked.com', ['@blocked']))->toBeFalse()
        // The default list no longer applies once a custom one is given.
        ->and(Email::validate('someone@mailinator.com', ['@blocked']))->toBe('someone@mailinator.com');
});

it('accepts plus addressing and long top-level domains', function () {
    expect(Email::validate('user+tag@example.com'))->toBe('user+tag@example.com')
        ->and(Email::validate('person@example.technology'))->toBe('person@example.technology');
});

it('shows an entity-encoded address without JavaScript', function () {
    $shown = Email::show('a@b.com');

    expect($shown)->toBe('a&#64;b&#46;com')
        ->and($shown)->not->toContain('<script')
        ->and($shown)->not->toContain('a@b.com');
});

it('encodes at-signs and dots as entities', function () {
    expect(Email::show('a@b.com'))->toBe('a&#64;b&#46;com');
});

it('returns false when showing an invalid address', function () {
    expect(Email::show('nope'))->toBeFalse()
        ->and(Email::mailto('nope'))->toBeFalse();
});

it('builds a normal mailto link without JavaScript', function () {
    $link = Email::mailto('a@b.com');

    expect($link)->toBe('<a href="mailto:a@b.com">a@b.com</a>')
        ->and($link)->not->toContain('document.write');
});

it('escapes link text and encodes the subject', function () {
    $link = Email::mailto('a@b.com', 'Write <me>', 'Hello world', 'class="x"');

    expect($link)->toContain('Write &lt;me&gt;')
        ->and($link)->toContain('subject=Hello%20world')
        ->and($link)->toContain('class="x"');
});

it('defaults the link text to the address itself', function () {
    expect(Email::mailto('a@b.com'))->toContain('>a@b.com</a>');
});

it('accepts attribute arrays and drops event handlers', function () {
    $link = Email::mailto('a@b.com', 'Contact', '', [
        'class' => 'contact',
        'aria-label' => 'Email us',
        'onclick' => 'alert(1)',
    ]);

    expect($link)->toContain('class="contact"')
        ->and($link)->toContain('aria-label="Email us"')
        ->and($link)->not->toContain('onclick');
});
