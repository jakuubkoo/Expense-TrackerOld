import { BrowserRouter as Router, Routes, Route} from 'react-router-dom'

/**
 * Component defining the routing structure of the application.
 */
export default function AppRouter() {
    return (
        <Router>
            <Routes>
                {/* default components */}
                {/*<Route exact path="/" element={<DashboardComponent/>}/>*/}

                {/*<Route path="*" element={<NotFoundComponent/>}/>*/}
            </Routes>
        </Router>
    )
}
