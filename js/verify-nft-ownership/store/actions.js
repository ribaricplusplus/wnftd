import apiFetch from '@wordpress/api-fetch';

import { errorFactory, getPublicAddress, signMessage } from '../util';

export function setUserLoggedIn( loggedIn ) {
	return {
		type: 'SET_USER_LOGGED_IN',
		loggedIn,
	};
}

export function setPublicAddress( publicAddress ) {
	return {
		type: 'SET_PUBLIC_ADDRESS',
		publicAddress,
	};
}

export function setNonces( nonces ) {
	return {
		type: 'SET_NONCES',
		nonces,
	};
}

export function setUserOwnedPublicAddresses( addresses ) {
	return {
		type: 'SET_USER_OWNED_PUBLIC_ADDRESSES',
		addresses,
	};
}

export function setMessageForSigning( message ) {
	return {
		type: 'SET_MESSAGE_FOR_SIGNING',
		message,
	};
}

/**
 * See Scripts_Loader::add_inline_data for initData contents.
 */
export function initStore( initData ) {
	return async ( { dispatch } ) => {
		await dispatch.setUserLoggedIn( initData.userLoggedIn );
		await dispatch.setUserOwnedPublicAddresses(
			initData.userOwnedPublicAddresses
		);
		await dispatch.setNonces( initData.nonces );
		await dispatch.setMessageForSigning( initData.messageForSigning );
	};
}

/**
 * See reducer.js for description of message object.
 */
export function addMessage( message ) {
	if ( ! message.severity ) {
		message.severity = 'info';
	}

	return {
		type: 'ADD_MESSAGES',
		messages: [ message ],
	};
}

export function clearMessages() {
	return {
		type: 'CLEAR_MESSAGES',
	};
}

export function requestProductDownload() {
	return async ( { dispatch, select } ) => {
		// Query the product download REST endpoint
		const productId = window?.wnftdData?.productId;

		if ( ! productId ) {
			throw new Error( 'Product ID not found.' );
		}

		const nonce = await select.getNonce( 'download' );

		const params = new URLSearchParams( {
			id: productId,
			wnftd_product_nonce: nonce,
		} );

		try {
			const response = await apiFetch( {
				path: `/wnftd/v1/products/download?${ params.toString() }`,
			} );

			if ( ! response.code === 'access_granted' ) {
				throw new errorFactory( 'nft_verification_failed' );
			}
		} catch ( e ) {
			throw new errorFactory( 'nft_verification_failed' );
		}
	};
}

export function addUserOwnedPublicAddress( publicAddress ) {
	return {
		type: 'ADD_USER_OWNED_PUBLIC_ADDRESSES',
		addresses: [ publicAddress ],
	};
}

export function requestPublicAddressVerification() {
	return async ( { dispatch, select, resolveSelect } ) => {
		const message = await select.getMessageForSigning();

		const account = await getPublicAddress();
		const signature = await signMessage( message );

		if ( ! signature ) {
			throw new Error( 'Failed to get signature.' );
		}

		const nonce = select.getNonce( 'auth' );

		const params = new URLSearchParams( {
			public_address: account,
			signature: signature,
			wnftd_auth_nonce: nonce,
		} );

		try {
			await apiFetch( { path: `/wnftd/v1/auth?${ params.toString() }` } );
			await dispatch.addUserOwnedPublicAddress( account );
		} catch ( e ) {
			const error = new Error( 'Signature verification failed.' );
			error.code = 'signature_verification';
			throw error;
		}

		return account;
	};
}

export function setSwitchingNetwork( isSwitching ) {
	return {
		type: 'SET_SWITCHING_NETWORK',
		isSwitching,
	};
}
