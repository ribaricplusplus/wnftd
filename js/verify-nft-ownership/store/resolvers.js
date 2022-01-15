export function getPublicAddress() {
	return async ( { dispatch } ) => {
		if ( ! window.ethereum ) {
			throw new Error();
		}

		try {
			const addresses = await window.ethereum.request( {
				method: 'eth_requestAccounts',
			} );
			await dispatch.setPublicAddress( addresses[ 0 ] );
		} catch ( error ) {
			await dispatch.addMessage( error.message );
		}
	};
}
