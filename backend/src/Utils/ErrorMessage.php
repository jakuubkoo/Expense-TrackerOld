<?php

namespace App\Utils;

class ErrorMessage {

    const INVALID_INPUT = 'The input provided is invalid.';
    const DATABASE_ERROR = 'There was an error connecting to the database.';
    const NO_FIRST_NAME = 'First name is required.';
    const NO_LAST_NAME = 'Last name is required.';
    const NO_EMAIL = 'Email is required.';
    const NO_PASSWORD = 'Password is required.';
    const NO_CONF_PASSWORD = 'Confirmation password is required.';
    const EMAIL_ALREADY_EXISTS = 'An account with this email already exists.';

    const PASSWORD_CONFIRMATION_MISMATCH = 'Password confirmation does not match.';
    const PASSWORD_TOO_SHORT = 'Password must be at least 8 characters long.';
    const ACCESS_DENIED = 'You do not have permission to access this resource.';
    const UNEXPECTED_REGISTER_ERROR = 'An unexpected error occurred during registration.';

    const UNEXPECTED_LOGOUT_ERROR = 'An unexpected error occurred during logout.';

}