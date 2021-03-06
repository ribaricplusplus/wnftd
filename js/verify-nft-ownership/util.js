import { ethers } from 'ethers';
import { dispatch, select } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { STORE_NAME } from './store';

let provider = null;
let signer = null;

const chainNames = {
	1: 'Ethereum Mainnet',
	137: 'Polygon Mainnet',
};

export function getChainName( id ) {
	if ( ! chainNames[ id ] ) {
		throw new Error( 'Invalid chain name ID.' );
	}

	return chainNames[ id ];
}

export function hasMetaMask() {
	return window.ethereum !== undefined && window.ethereum.isMetaMask;
}

/**
 * Some error codes and needed additional data:
 *
 * - chain_id_mismatch
 *   - productChainId: number
 */
export function errorFactory( name, additionalData = {} ) {
	const error = new Error();
	error.code = name;
	error.additionalData = additionalData;
	return error;
}

export async function getWeb3Provider() {
	if ( provider ) {
		return provider;
	}

	if ( ! hasMetaMask() ) {
		throw new Error();
	}

	await window.ethereum.request( { method: 'eth_requestAccounts' } );

	provider = new ethers.providers.Web3Provider( window.ethereum );

	window.ethereum.on( 'accountsChanged', () => {
		window.location.reload();
	} );

	window.ethereum.on( 'chainChanged', () => {
		window.location.reload();
	} );

	return provider;
}

export async function getWeb3Signer() {
	if ( signer ) {
		return signer;
	}

	if ( ! provider ) {
		await getWeb3Provider();
	}

	signer = await provider.getSigner();

	const productChainId = getChainId();
	const signerChainId = await signer.getChainId();

	if ( productChainId !== signerChainId ) {
		try {
			await dispatch( STORE_NAME ).addMessage( {
				name: 'chain_id_mismatch',
				message: getNetworkSwitchMessage( productChainId ),
			} );

			const results = await Promise.all( [
				window.ethereum.request( {
					method: 'wallet_switchEthereumChain',
					params: [
						{ chainId: '0x' + productChainId.toString( 16 ) },
					],
				} ),
				dispatch( STORE_NAME ).setSwitchingNetwork( true ),
			] );

			if ( results[ 0 ] !== null ) {
				throw new Error();
			} else {
				window.location.reload();
			}
		} catch ( e ) {
			signer = null;
			throw new errorFactory( 'chain_id_mismatch', {
				productChainId,
			} );
		}
	}

	return signer;
}

export async function signMessage( message ) {
	if ( await select( STORE_NAME ).isSwitchingNetwork() ) {
		throw new Error();
	}

	const _signer = await getWeb3Signer();

	return _signer.signMessage( message );
}

export function getChainId() {
	if ( window?.wnftdData?.chainId === undefined ) {
		throw new Error( 'Missing chain id' );
	}

	if ( window.wnftdData.chainId === 0 ) {
		throw new Error( 'Invalid chain id' );
	}

	return window.wnftdData.chainId;
}

export async function getPublicAddress() {
	const signer = await getWeb3Signer();
	const publicAddress = ( await signer.getAddress() ).toLowerCase();
	return publicAddress;
}

export function getNetworkSwitchMessage( chainId ) {
	return sprintf(
		__(
			'Your wallet account is not on the same network as the required NFTs. Please use an account connected to %s.',
			'wnftd'
		),
		getChainName( chainId )
	);
}
