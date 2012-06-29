Feature: AccountUpdater
  Tests to verify AccountUpdater updates credit card correctly.

  Background:
    Given I am doing cc or echeck transactions
    And I am doing non paypage transactions

  @javascript @creditcard @ready
  Scenario: Do a successful checkout and then capture the auth
  Given I am logged in as an administrator
  	When I view "Catalog" "Manage Products"
  	  And I click on subscribed thing
  	  And I select "Yes" from the dropbox "litle_subscription"
  	  And I click on save
  	And I follow "Log Out"
  Given I am doing Litle auth
    And I am logged in as "gdake@litle.com" with the password "password"
    When I have "subscribed thing" in my cart
      And I press "Proceed to Checkout"
      And I press "Continue"
      And I press the "3rd" continue button
      And I choose "CreditCard"
      And I select "Visa" from "Credit Card Type"
      And I put in "Credit Card Number" with "4100000000000001"
      And I select "9" from "Expiration Date"
      And I select "2012" from "creditcard_expiration_yr"
      And I put in "Card Verification Number" with "123"
      And I press the "4th" continue button
      And I press "Place Order"
    Then I should see "Thank you for your purchase"
      And I follow "Log Out"
    Given I am logged in as an administrator
  	When I view "Customers" "Manage Customers"
  	  And I click on the customer "Greg Dake" in "Manage Customers"
  	  Then I should see "Personal Information"
  	  And I follow "Litle & Co. Subscription"
  	  Then I should see "subscribed thing"
    When I view "Sales" "Litle & Co" "Subscriptions"
      Then I should see "Subscriptions"
      And I click on the top row in Subscriptions
        Then I should see "Subscription Details"
     	Then I should see "subscribed thing"
     	And I write in "Recurring Fees" with "9.99"
     	And I select "Weekly" from "billing_period"
     	And I write in "Total Number of Billing Cycles" with "79"
     	And I click on save subscription
     	Then I should see "9.99" in the "recurring_fees"
     	Then I should see "Weekly" in the "billing_period"
     	Then I should see "79" in the "total_number_of_billing_cycles"
     	And I click on cancel subscription
     	Then I should see "Subscription is not active."
  	And I follow "Log Out"