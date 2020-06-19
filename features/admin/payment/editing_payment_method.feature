@managing_payment_methods
Feature: Editing payment methods
    In order to change which payment methods are available in my store
    As an Administrator
    I want to be able to edit payment method

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a payment method "Mercado Pago" with a code "MP" and Mercado Pago gateway
        And I am logged in as an administrator

    @ui
    Scenario: Renaming the payment method
        Given I want to modify the "Mercado Pago" payment method
        When I rename it to "Mercado Pago Checkout" in "English (United States)"
        And I save my changes
        Then I should be notified that it has been successfully edited
        And this payment method name should be "Mercado Pago Checkout"

    @ui
    Scenario: Disabling payment method
        Given I want to modify the "Mercado Pago" payment method
        When I disable it
        And I save my changes
        Then I should be notified that it has been successfully edited
        And this payment method should be disabled

    @ui
    Scenario: Enabling payment method
        Given the payment method "Mercado Pago" is disabled
        And I want to modify the "Mercado Pago" payment method
        When I enable it
        And I save my changes
        Then I should be notified that it has been successfully edited
        And this payment method should be enabled

    @ui
    Scenario: Seeing disabled code field while editing payment method
        When I want to modify the "Mercado Pago" payment method
        Then the code field should be disabled

    @ui
    Scenario: Seeing disabled gateway factory field while editing payment method
        When I want to modify the "Mercado Pago" payment method
        Then the factory name field should be disabled
