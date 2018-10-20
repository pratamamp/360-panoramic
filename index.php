<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
	<title>Hello World</title>
	<style>
		body{
			color: #000;
			font-family:Monospace;
			font-size:13px;
			margin: 0px;
			overflow: hidden;
		}

		#info {
				position: absolute;
				top: 0px; width: 100%;
				text-align:center;
				padding: 5px;
			}
	</style>
</head>
<body>
	<div id="container"></div>
	<div id="info"><a href="#">Test Panoramic</a></div>
	<script src="js/three.min.js"></script>
	<script src="js/controls/OrbitControls.js"></script>
	<script src="js/objects/Water.js"></script>
	<script src="js/objects/Sky.js"></script>
	<script src="js/WebGL.js"></script>

	<script>
		if (WEBGL.isWebGLAvailable() === false) {
			document.body.appendChild( WEBGL.getWebGLErrorMessage() )
		}

		let container, stats
		let camera, scene, renderer, light
		let controls, water, sphere

		init()
		animate()


		function init() {
			container = document.getElementById('container')
			// 

			renderer = new THREE.WebGLRenderer()
			renderer.setPixelRatio(window.devicePixelRatio)
			renderer.setSize(window.innerWidth, window.innerHeight)
			container.appendChild(renderer.domElement)

			scene = new THREE.Scene()
			//

			camera = new THREE.PerspectiveCamera(55, window.innerWidth/ window.innerHeight, 1, 20000)
			camera.position.set(30, 30, 100)

			light = new THREE.DirectionalLight(0xffffff, 0.8)
			scene.add(light)

			// Water

			let waterGeometry = new THREE.PlaneBufferGeometry(10000, 10000)
			water = new THREE.Water(
				waterGeometry, {
					textureWidth: 512,
					textureHeight: 512,
					waterNormals: new THREE.TextureLoader().load('textures/waternormals.jpg', function(texture) {
						texture.wrapS = texture.wrapT = THREE.RepeatWrapping
					}),
					alpha: 1.0,
					sunDirection: light.position.clone().normalize(),
					sunColor:0xffffff,
					waterColor:0x001e0f,
					distortionScale: 3.7,
					fog:scene.fog !== undefined
				}
			)
			water.rotation.x =- Math.PI / 2
			scene.add(water)

			// Skybox

			let sky = new THREE.Sky()
			sky.scale.setScalar(10000)
			scene.add(sky)

			let uniforms = sky.material.uniforms

			uniforms.turbidity.value = 10
			uniforms.rayleigh.value = 2
			uniforms.luminance.value = 1
			uniforms.mieCoefficient.value = 0.005
			uniforms.mieDirectionalG.value = 0.8

			let parameters =  {
				distance:400,
				inclination:0.49,
				azimuth : 0.205
			}

			let cubeCamera = new THREE.CubeCamera(1, 20000, 256)
			cubeCamera.renderTarget.texture.minFilter = THREE.LinearMipMapLinearFilter

			function updateSun() {

					var theta = Math.PI * ( parameters.inclination - 0.5 )
					var phi = 2 * Math.PI * ( parameters.azimuth - 0.5 )

					light.position.x = parameters.distance * Math.cos( phi )
					light.position.y = parameters.distance * Math.sin( phi ) * Math.sin( theta )
					light.position.z = parameters.distance * Math.sin( phi ) * Math.cos( theta )

					sky.material.uniforms.sunPosition.value = light.position.copy( light.position )
					water.material.uniforms.sunDirection.value.copy( light.position ).normalize()

					cubeCamera.update( renderer, scene )

				}

				updateSun()

				controls = new THREE.OrbitControls(camera, renderer.domElement)
				controls.maxPolarAngle = Math.PI * 0.495
				controls.target.set(0, 10, 0)
				controls.minDistance = 40.0
				controls.maxDistance = 200.0
				camera.lookAt(controls.target)

				window.addEventListener('resize', onWindowResize, false)

		}

		function onWindowResize() {
			camera.aspect = window.innerWidth / window.innerHeight
			camera.updateProjectionMatrix()
			renderer.setSize(window.innerWidth, window.innerHeight)
		}

		function animate() {
			requestAnimationFrame(animate)
			render()
			
		}

		function render() {
			let time = performance.now() * 0.001

			water.material.uniforms.time.value += 1.0/60.0
			renderer.render(scene,camera)
		}
	</script>
</body>
</html>