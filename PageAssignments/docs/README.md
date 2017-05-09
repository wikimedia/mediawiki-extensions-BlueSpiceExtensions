## Future development

At the moment a assigned user always receives special permission. This is
independent from the way a user is being assigned (directly as a user,
indirectly by group membership, indirectly by assignment of 'everyone').

In case of 'everyone' this may be a problem. But as 'everyone' is not available
by default we leave it that way for now.

For future development the different assignment types ( $bsgPageAssigneeTypes ),
should be able to define what they enable for the user. E. g.

- Notification on changes to the assigned page
- Read confirmation of assigned pages
- Special permissions on the assigned page

This should be implemented in the 'BSAssignableBase' derived classes.
Maybe having a hook in "BSAssignableBase::factory" could also allow other
extensions to decorate the different types, thus adding capabilities.
