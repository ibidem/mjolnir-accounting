Implementation Details and Key Notes
====================================

 - any operation that does not satisfy A = L + OE - W + R - E and variants of 
   the formula are strictly forbidden with out exception

 - all TAccounts have associated a Type; the type also has a subtype hint for
   visual representation purposes—only the type is used in formulas

 - TAccounts have a sign which affects their debit/credit calculations, a 
   negative TAccount is essentially a "contra" account

 - TAccounts may have any number of children

 - TAccounts do NOT hold at any point value directly

 - value associated with a TAccount is always in it's children

 - if a TAccount is split into multiple children the associated entries for it 
   are refactored to transfer all values among it's children

 - if a new child needs to be added to a TAccount but it does not inherit the 
   value of the TAccount an "Other" TAccount child is automatically created

 - all system transactions regardless of visual representation are handled 
   though journalization

 - TAccount hierarchies are handled though nested sets (normal ones, not the 
   floating point variant)
