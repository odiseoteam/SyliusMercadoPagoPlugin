@managing_payment_methods
Feature: Browsing payment methods
    In order to see all payment methods in the store
    As an Administrator
    I want to be able to browse payment methods

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: Browsing defined payment methods
        Given the store has a payment method "Offline" with a code "OFF"
        And the store has a payment method "Mercado Pago" with a code "MP" and Mercado Pago gateway
        When I browse payment methods
        Then I should see 2 payment methods in the list
        And the payment method "Mercado Pago" should be in the registry
