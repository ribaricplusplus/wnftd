import { uniqBy, uniq } from 'lodash';
import { combineReducers } from '@wordpress/data';

function isUserLoggedIn( state = false, action ) {
	switch ( action.type ) {
		case 'SET_USER_LOGGED_IN':
			return action.loggedIn;
	}

	return state;
}

function publicAddress( state = null, action ) {
	switch ( action.type ) {
		case 'SET_PUBLIC_ADDRESS':
			return action.publicAddress;
	}

	return state;
}

function nonces( state = {}, action ) {
	switch ( action.type ) {
		case 'SET_NONCES':
			return {
				...state,
				...action.nonces,
			};
	}

	return state;
}

function userOwnedPublicAddresses( state = [], action ) {
	switch ( action.type ) {
		case 'SET_USER_OWNED_PUBLIC_ADDRESSES':
			return action.addresses;
		case 'ADD_USER_OWNED_PUBLIC_ADDRESSES':
			return uniq( [ ...state, ...action.addresses ] );
	}

	return state;
}

/**
 * Messages are an array of object with the following properties:
 * - name => Unique ID for a message
 * - message
 * - severity => Material UI severity. info default.
 */
function messages( state = [], action ) {
	switch ( action.type ) {
		case 'ADD_MESSAGES':
			return uniqBy( [ ...state, ...action.messages ], 'name' );
		case 'CLEAR_MESSAGES':
			return [];
	}

	return state;
}

function messageForSigning( state = '', action ) {
	switch ( action.type ) {
		case 'SET_MESSAGE_FOR_SIGNING':
			return action.message;
	}

	return state;
}

export default combineReducers( {
	isUserLoggedIn,
	publicAddress,
	nonces,
	messages,
	userOwnedPublicAddresses,
	messageForSigning,
} );
