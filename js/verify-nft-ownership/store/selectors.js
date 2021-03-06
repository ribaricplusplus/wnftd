export function isUserLoggedIn( state ) {
	return state.isUserLoggedIn ?? false;
}

export function getNonce( state, nonce ) {
	return state.nonces[ nonce ] ?? null;
}

export function getNonces( state ) {
	return state.nonces;
}

export function getMessages( state ) {
	return state.messages;
}

export function getUserOwnedPublicAddresses( state ) {
	return state.userOwnedPublicAddresses;
}

export function getMessageForSigning( state ) {
	return state.messageForSigning;
}

export function isSwitchingNetwork( state ) {
	return state.isSwitchingNetwork;
}
