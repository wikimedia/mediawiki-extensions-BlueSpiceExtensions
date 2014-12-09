$( document ).ready( function () {
	var BSShoutboxMentions = {
		mentions: [ ],
		//matches whole words starting with @
		match: /\B@(\w*)$/,
		search: function ( term, callback ) {
			//if the mentions array is empty get a list of all users available
			//first trigger with the @ in the shoutbox to not overload requests
			if ( BSShoutboxMentions.mentions.length === 0 ) {
				this.getUsers();
			}
			callback( $.map( BSShoutboxMentions.mentions, function ( mention ) {
				//returns matches for names containing the term + case insensitive
				return mention.toLowerCase().indexOf( term.toLowerCase() ) !== -1 ? mention : null;
			} ) );
		},
		index: 1,
		replace: function ( mention ) {
			//put the username in the shoutbox, not the displayname for better usage later
			var username = mention.match( /\((.*?)\)/ );
			return '@' + username[1] + ' ';
		},
		getUsers: function () {
			$.getJSON( bs.util.getCAIUrl( 'getUserStoreData' ), function ( data ) {
				var users = [ ];
				$.each( data.users, function ( i, v ) {
					users.push( v.display_name + " (" + v.user_name + ")" );
				} );
				BSShoutboxMentions.mentions = users;
			} );
		}
	}

	var strategies = [
		BSShoutboxMentions
	];
	$( '#bs-sb-message' ).textcomplete( strategies );
} );