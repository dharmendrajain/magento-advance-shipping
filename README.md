# magento-advance-shipping
Magento Advance Shipping Extension ( Calculate Shipping based on percentage )

1) Create custom shipping method.
2) Set condition where shipping method will be show on the  basis of customer group
3) It Should be managed from Store Configuration.
4) Shipping method should be calculated on basis of 5% of subtotal amount.
5) Create A Custom Product Boolean Attribute named "Ignore From Shipping"
6) If the value of "Ignore from shipping" is Yes then ignore it from Shipping calculation
7) Create Another attribute called "Add Shipping Price".
8 ) If any product has value in "Add Shipping Price" then at the time
of calculation you need to calculate added shipping price value and for remaining
product 5% of remaining product subtotal should be calculated.
9) And also what percentage should I give 5% or any other % it should be dynamically set from store configuration.