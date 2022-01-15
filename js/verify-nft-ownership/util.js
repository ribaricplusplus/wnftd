export function hasMetaMask() {
	return window.ethereum !== undefined;
}

export function errorFactory( name ) {
	const error = new Error();
	error.code = name;
	return error;
}
